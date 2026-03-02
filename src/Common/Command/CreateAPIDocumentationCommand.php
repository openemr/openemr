<?php

/**
 * CreateAPIDocumentation.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Generator;
use OpenApi\Processors\DocBlockDescriptions;
use OpenApi\Processors\OperationId;
use OpenApi\SourceFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
        // Use $GLOBALS directly instead of OEGlobalsBag because bin/console
        // sets up $GLOBALS['fileroot'] before the command runs, but OEGlobalsBag
        // may not be fully initialized when --skip-globals is used.
        /** @var string $fileroot */
        $fileroot = $GLOBALS['fileroot'];
        $fileDestinationFolder = $fileroot . DIRECTORY_SEPARATOR . "swagger" . DIRECTORY_SEPARATOR;
        $fileDestinationYaml =  $fileDestinationFolder . "openemr-api.yaml";

        $generator = new Generator();
        // Use only AttributeAnnotationFactory to avoid picking up docblock comments as summaries
        $analyser = new ReflectionAnalyser([
            new AttributeAnnotationFactory(),
        ]);
        $analyser->setGenerator($generator);

        // Remove processors that add fields not in the original swagger output
        $generator->getProcessorPipeline()->remove(DocBlockDescriptions::class);
        $generator->getProcessorPipeline()->remove(OperationId::class);

        $openapi = $generator
            ->setAnalyser($analyser)
            ->generate(new SourceFinder([
                $fileroot . '/_rest_routes.inc.php',
                $fileroot . '/apis/routes',
                $fileroot . '/src/RestControllers',
            ]));

        if ($openapi === null) {
            $output->writeln("Failed to generate OpenAPI documentation");
            return Command::FAILURE;
        }

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
