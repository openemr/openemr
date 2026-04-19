<?php

/**
 * CLI command to import documents from the configured scanner directory.
 *
 * Replaces the legacy custom/zutil.cli.doc_import.php script that was
 * invoked via library/allow_cronjobs.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DocumentImportCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    /**
     * Fallback mime-type lookup by extension, used when finfo cannot detect
     * the mime type of a file. Preserved from the legacy script.
     */
    private const EXT_TO_MIME = [
        'pdf' => 'application/pdf',
        'exe' => 'application/octet-stream',
        'zip' => 'application/zip',
        'docx' => 'application/msword',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpeg' => 'image/jpg',
        'jpg' => 'image/jpg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/x-wav',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        '3gp' => 'video/3gpp',
        'css' => 'text/css',
        'jsc' => 'application/javascript',
        'js' => 'application/javascript',
        'php' => 'text/html',
        'htm' => 'text/html',
        'html' => 'text/html',
    ];

    protected function configure(): void
    {
        $this
            ->setName('documents:import')
            ->setDescription(
                'Import documents from the configured scanner directory. '
                . 'The source path is read from the scanner_output_directory global '
                . 'and cannot be overridden on the command line.'
            )
            ->setDefinition(
                new InputDefinition([
                    new InputOption('pid', null, InputOption::VALUE_REQUIRED, 'Patient ID to assign to each imported document', '00'),
                    new InputOption('category', null, InputOption::VALUE_REQUIRED, 'Category name (url-encoded) or numeric id', '1'),
                    new InputOption('owner', null, InputOption::VALUE_REQUIRED, 'Owner user id to attribute the import to', '0'),
                    new InputOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of files to import', '10'),
                    new InputOption('in-situ', null, InputOption::VALUE_NONE, 'Create document records without moving files out of the scanner directory'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        require_once __DIR__ . '/../../../library/documents.php';

        $io = new SymfonyStyle($input, $output);
        $globals = $this->getGlobalsBag();

        $path = $globals->getString('scanner_output_directory');
        if ($path === '') {
            $io->error('Global "scanner_output_directory" is not configured.');
            return Command::FAILURE;
        }
        if (!is_dir($path)) {
            $io->error("Scanner directory does not exist: {$path}");
            return Command::FAILURE;
        }

        $pid = $this->stringOption($input, 'pid', '00');
        $ownerRaw = $this->stringOption($input, 'owner', '0');
        $owner = ctype_digit($ownerRaw) ? (int) $ownerRaw : 0;
        $limit = max(0, (int) $this->stringOption($input, 'limit', '10'));
        $inSitu = (bool) $input->getOption('in-situ');
        $categoryId = $this->resolveCategoryId($this->stringOption($input, 'category', '1'));

        $io->writeln(sprintf(
            '%s %s %s (%s)',
            xlt('Import'),
            text((string) $limit),
            xlt('documents'),
            text($path),
        ));

        $remaining = $limit;
        foreach (new \DirectoryIterator($path) as $doc) {
            if ($doc->isDot() || !$doc->isFile()) {
                continue;
            }
            if ($remaining <= 0) {
                break;
            }

            $docPath = $doc->getPathname();
            $mime = $this->detectMimeType($docPath, $doc->getExtension());

            if ($inSitu) {
                $this->importInSitu($docPath, $mime, $doc->getSize(), $pid, $owner, $categoryId, $io);
            } else {
                if (!$this->importAndMove($doc->getFilename(), $docPath, $mime, $doc->getSize(), $pid, $owner, $io)) {
                    return Command::FAILURE;
                }
            }
            $remaining--;
        }

        return Command::SUCCESS;
    }

    private function stringOption(InputInterface $input, string $name, string $default): string
    {
        $value = $input->getOption($name);
        return is_string($value) ? $value : $default;
    }

    private function resolveCategoryId(string $raw): int
    {
        if (ctype_digit($raw)) {
            return (int) $raw;
        }
        $row = QueryUtils::fetchRecords('SELECT id FROM categories WHERE name=? LIMIT 1', [urldecode($raw)]);
        if (isset($row[0]['id']) && is_numeric($row[0]['id'])) {
            return (int) $row[0]['id'];
        }
        return 1;
    }

    private function detectMimeType(string $path, string $extension): string
    {
        $finfo = finfo_open();
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $path, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }
        return self::EXT_TO_MIME[strtolower($extension)] ?? 'application/octet-stream';
    }

    private function importInSitu(
        string $docPath,
        string $mime,
        int $size,
        string $pid,
        int $owner,
        int $categoryId,
        SymfonyStyle $io,
    ): void {
        $docUrl = 'file://' . $docPath;
        $existing = QueryUtils::fetchRecords(
            'SELECT id FROM documents WHERE url=? OR url=? LIMIT 1',
            [$docPath, $docUrl],
        );
        if (isset($existing[0])) {
            return;
        }

        $doc = new Document();
        $doc->set_storagemethod('0');
        $doc->set_mimetype($mime);
        $doc->set_url($docUrl);
        $doc->set_size((string) $size);
        $hash = hash_file('sha3-512', $docPath);
        $doc->set_hash($hash === false ? '' : $hash);
        $typeArray = $doc->type_array;
        $fileUrlType = is_array($typeArray) ? ($typeArray['file_url'] ?? '') : '';
        $doc->set_type($fileUrlType);
        $doc->set_owner((string) $owner);
        $doc->set_foreign_id($pid);
        $doc->persist();
        $doc->populate();

        $id = $doc->get_id();
        if (is_numeric($id)) {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO categories_to_documents(category_id, document_id) VALUES(?,?)',
                [$categoryId, $id],
            );
            $url = $doc->get_url();
            $urlString = is_string($url) ? $url : '';
            $io->writeln(sprintf('%s - %s', text($docPath), text($urlString)));
        } else {
            $io->writeln(sprintf('%s - %s', text($docPath), xlt('Documents setup error')));
        }
    }

    private function importAndMove(
        string $name,
        string $docPath,
        string $mime,
        int $size,
        string $pid,
        int $owner,
        SymfonyStyle $io,
    ): bool {
        $result = \addNewDocument(
            name: $name,
            type: $mime,
            tmp_name: $docPath,
            error: '',
            size: (string) $size,
            owner: $owner,
            patient_id_or_simple_directory: $pid,
            category_id: 1,
            higher_level_path: '',
            path_depth: 1,
            skip_acl_check: true,
        );

        if (!is_array($result)) {
            $io->writeln(sprintf('%s - %s', text($docPath), xlt('Documents setup error')));
            return false;
        }

        $url = is_string($result['url'] ?? null) ? $result['url'] : '';
        $io->writeln(sprintf('%s - %s', text($docPath), text($url)));

        if (!unlink($docPath)) {
            $io->writeln(sprintf('%s - %s', text($docPath), xlt('Original file deletion error')));
            return false;
        }
        return true;
    }
}
