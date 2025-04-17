<?php

/**
 * CreateAPIDocumentation.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAPIDocumentationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('openemr:create-api-documentation')
            ->setDescription("Generates an OpenAPI swagger file that documents the OpenEMR API")
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routesLocation = $GLOBALS['fileroot'] . DIRECTORY_SEPARATOR . "_rest_routes.inc.php";
        $fileDestinationFolder = $GLOBALS['fileroot'] . DIRECTORY_SEPARATOR . "swagger" . DIRECTORY_SEPARATOR;
        $fileDestinationYaml =  $fileDestinationFolder . "openemr-api.yaml";
        $site = $input->getOption('site') ?? 'default';

        $openapi = \OpenApi\Generator::scan([$routesLocation]);

        $resultYaml = file_put_contents($fileDestinationYaml, $openapi->toYaml());

        if ($resultYaml === false) {
            $output->writeln("No write access to " . $fileDestinationYaml);
            return Command::FAILURE;
        } else {
            $output->writeln("API file generated at " . $fileDestinationYaml);
            $output->writeln("Your API documentation can now be viewed by going to <webroot>/swagger/");
            $output->writeln("For example on the easy docker installation this would be https://localhost:9300/swagger/");
            return Command::SUCCESS;
        }
    }
}
