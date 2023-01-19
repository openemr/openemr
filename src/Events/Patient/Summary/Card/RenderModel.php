<?php

/**
 * This file is part of OpenEMR.
 *
 * @link      https://github.com/openemr/openemr/tree/master
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @package   OpenEMR\Events\Patient\Summary\Card
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 */

namespace OpenEMR\Events\Patient\Summary\Card;

class RenderModel implements RenderInterface
{
    private $templateFileName;

    private $variables;

    public function __construct(string $templateFileName, array $variables)
    {
        $this->templateFileName = $templateFileName;
        $this->variables = $variables;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateFile(): string
    {
        return $this->templateFileName;
    }

    /**
     * @inheritDoc
     */
    public function getVariables(): array
    {
        return $this->variables;
    }
}
