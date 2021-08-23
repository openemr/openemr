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


use OpenEMR\Common\Command\Runner\CommandContext;

class CreateAPIDocumentationCommand implements IOpenEMRCommand
{

    /**
     * Prints the instructions on how to use this command
     * @param CommandContext $context All the context about the command environment.
     */
    public function printUsage(CommandContext $context)
    {
        echo "Command Usage: " . $context->getScriptName() . " -c CreateAPIDocumentation" . "\n";
    }

    /**
     * Returns a description of the command
     * @return string
     */
    public function getDescription(CommandContext $context): string
    {
        return "Generates an OpenAPI swagger file that documents the OpenEMR API";
    }

    /**
     * Execute the command and spit any output to STDOUT and errors to STDERR
     * @param CommandContext $context All the context information needed for the CLI Command to execute
     */
    public function execute(CommandContext $context)
    {
        $routesLocation = $context->getRootPath() . "_rest_routes.inc.php";
        $fileDestinationFolder = $context->getRootPath();
        $fileDestination =  $fileDestinationFolder . "openemr-api-swagger.json";


        $openapi = \OpenApi\Generator::scan([$routesLocation]);

        $result = file_put_contents($fileDestination, $openapi->toJson());
        if ($result === false)
        {
            echo "No write access to " . $fileDestination ."\n";
            $this->printUsage($context);
            return;
        } else {
            echo "API file generated at " . $fileDestination . "\n";
            echo "Your API documentation can now be viewed by going to <SITE_URL>/swagger/\n";
            echo "For example on the easy docker installation this would be https://localhost:9300/swagger/\n";
        }
    }
}