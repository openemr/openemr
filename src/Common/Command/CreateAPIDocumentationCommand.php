<?php

/**
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use OpenApi\Generator as OpenApiGenerator;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class CreateAPIDocumentationCommand extends Command
{
    private readonly string $fileroot;

    public function __construct()
    {
        parent::__construct();

        $this->fileroot = OEGlobalsBag::getInstance()->getString('fileroot');
    }

    protected function configure(): void
    {
        $this
            ->setName('openemr:create-api-documentation')
            ->setDescription("Generates an OpenAPI swagger file that documents the OpenEMR API")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routesLocation = $this->fileroot . DIRECTORY_SEPARATOR . "_rest_routes.inc.php";
        $fileDestinationFolder = $this->fileroot . DIRECTORY_SEPARATOR . "swagger" . DIRECTORY_SEPARATOR;
        $fileDestinationYaml =  $fileDestinationFolder . "openemr-api.yaml";

        $finder = new Finder();
        $finder
            ->in($this->fileroot . '/apis/routes')
            ->name('*.php')
        ;

        $openapi = OpenApiGenerator::scan(array_merge(
            [$routesLocation],
            iterator_to_array($finder->getIterator()),
        ));

        // To have smaller diffs - we force stable order here
        $data = json_decode($openapi->toJson(), true);

        foreach (['paths', 'components'] as $section) {
            ksort($data[$section]);
            foreach ($data[$section] as &$sectionItem) {
                ksort($sectionItem);
            }
        }

        if (isset($data['tags'])) {
            usort(
                $data['tags'],
                static fn($a, $b): int => strcmp((string) $a['name'], (string) $b['name'])
            );
        }

        $resultYaml = file_put_contents(
            $fileDestinationYaml,
            Yaml::dump($data, 20, 2)
        );

        if ($resultYaml === false) {
            $output->writeln(sprintf(
                'No write access to %s',
                $fileDestinationYaml,
            ));
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            'API file generated at %s',
            $fileDestinationYaml,
        ));
        $output->writeln('Your API documentation can now be viewed by going to <webroot>/swagger/');
        $output->writeln('For example on the easy docker installation this would be https://localhost:9300/swagger/');

        return Command::SUCCESS;
    }
}
