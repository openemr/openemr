<?php

/**
 * One provider drawing background exposed by the Eye Form image library.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use JsonSerializable;

final readonly class DrawingBackgroundOption implements JsonSerializable
{
    /** Describe an image choice and whether it supports structured drawing or saved-library actions. */
    public function __construct(
        public string $key,
        public string $label,
        public string $url,
        public bool $structured,
        public bool $saved = false,
        public bool $archived = false,
    ) {
    }

    /**
     * Expose the image choice to the browser, omitting inactive saved and archived flags.
     *
     * @return array{key:string,label:string,url:string,structured:bool,saved?:true,archived?:true}
     */
    public function jsonSerialize(): array
    {
        $option = ['key' => $this->key, 'label' => $this->label, 'url' => $this->url, 'structured' => $this->structured];
        if ($this->saved) {
            $option['saved'] = true;
        }
        if ($this->archived) {
            $option['archived'] = true;
        }
        return $option;
    }
}
