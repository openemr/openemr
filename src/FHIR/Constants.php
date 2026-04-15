<?php

declare(strict_types=1);

namespace OpenEMR\FHIR;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

final class Constants
{
    // PHPFHIR
    public const CODE_GENERATION_DATE = 'April 15th, 2026 16:02+0000';

    // Common
    public const JSON_FIELD_RESOURCE_TYPE = 'resourceType';
    public const JSON_FIELD_FHIR_COMMENTS = 'fhir_comments';

    // Date and time formats
    public const DATE_FORMAT_YEAR = 'Y';
    public const DATE_FORMAT_YEAR_MONTH = 'Y-m';
    public const DATE_FORMAT_YEAR_MONTH_DAY = 'Y-m-d';
    public const DATE_FORMAT_YEAR_MONTH_DAY_TIME = 'Y-m-d\TH:i:s\.uP';
    public const DATE_FORMAT_INSTANT = 'Y-m-d\TH:i:s\.uP';
    public const TIME_FORMAT = 'H:i:s';

    // Validation
    public const UNLIMITED = -1;
}
