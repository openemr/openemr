<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\Cqm\Qdm\BaseTypes;

use JsonSerializable;

class Code implements JsonSerializable
{
    public function __construct(
        public ?string $code = null,
        public ?string $system = null,
        public ?string $display = null,
        public ?string $version = null,
    ) {
    }

    /**
     * @return array{code: ?string, system: ?string, display: ?string, version: ?string, _type: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'system' => $this->system,
            'display' => $this->display,
            'version' => $this->version,
            '_type' => 'QDM::Code',
        ];
    }
}
