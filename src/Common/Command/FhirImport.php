<?php

/**
 * FhirImport command.
 *
 * Walks Synthea FHIR R4 Bundle *.json files and POSTs each entry to the
 * per-resource FHIR write endpoints added in #13281. Driven by
 * openemr-cmd irp --format=fhir (from the openemr-devops repo) through
 * docker/flex/utilities/devtoolsLibrary.source::importRandomPatients.
 *
 * See src/Services/FHIR/Import/README.md for the full readiness checklist
 * of server-side bugs, strict validators, unimplemented resources, and
 * the peelable transforms that work around each.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Services\FHIR\Import\FhirBundleImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class FhirImport extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:fhir-import')
            ->setDescription('Import Synthea FHIR R4 Bundle *.json files via the FHIR write endpoints')
            ->addUsage('--site=default --sourcePath=/tmp/synthea/output/fhir')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('sourcePath', null, InputOption::VALUE_REQUIRED, 'Directory of Synthea FHIR Bundle *.json files'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'OpenEMR site id', 'default'),
                    new InputOption('openemrPath', null, InputOption::VALUE_REQUIRED, 'OpenEMR web root', '/var/www/localhost/htdocs/openemr'),
                    new InputOption('baseUrl', null, InputOption::VALUE_REQUIRED, 'Base URL of the running stack', 'https://localhost'),
                    new InputOption('adminUser', null, InputOption::VALUE_REQUIRED, 'Admin username for password grant', 'admin'),
                    new InputOption('adminPass', null, InputOption::VALUE_REQUIRED, 'Admin password for password grant', 'pass'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Dev-only gate, same shape as ccda-import. Prevents accidental
        // invocation on a non-dev stack where the password grant and the
        // oauth_clients UPDATE would be inappropriate.
        if (getenv('OPENEMR_ENABLE_FHIR_IMPORT') === false) {
            $output->writeln('Set OPENEMR_ENABLE_FHIR_IMPORT=1 environment variable to enable this command');
            return 2;
        }

        $sourcePath = $input->getOption('sourcePath');
        if (!is_string($sourcePath) || $sourcePath === '') {
            $output->writeln('sourcePath parameter is missing (required)');
            return 2;
        }

        $importer = new FhirBundleImporter(
            baseUrl: $this->requireString($input, 'baseUrl'),
            site: $this->requireString($input, 'site'),
            openemrPath: $this->requireString($input, 'openemrPath'),
            adminUser: $this->requireString($input, 'adminUser'),
            adminPass: $this->requireString($input, 'adminPass'),
        );
        return $importer->run($sourcePath);
    }

    private function requireString(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Option --$name must be a string");
        }
        return $value;
    }
}
