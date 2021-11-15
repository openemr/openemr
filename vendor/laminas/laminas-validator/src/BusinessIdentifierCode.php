<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

class BusinessIdentifierCode extends AbstractValidator
{
    public const INVALID = 'valueNotBic';
    public const NOT_STRING = 'valueNotString';
    public const NOT_VALID_COUNTRY = 'valueNotCountry';

    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::NOT_STRING => 'Invalid type given; string expected',
        self::INVALID => 'Invalid BIC format',
        self::NOT_VALID_COUNTRY => 'Invalid country code',
    ];

    /**
     * @see https://www.bundesbank.de/resource/blob/749660/d2c6e00664251b4d83483c229e084e44/mL/technische-spezifikationen-scc-anhang-112018-data.pdf (page 39)
     */
    private const REGEX_BIC = '/^[a-z]{4}(?<country>[a-z]{2})[a-z2-9][a-np-z0-9]([0-9a-z]{3})?$/i';

    /**
     * List of all country codes defined by ISO 3166-1 alpha-2
     * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Current_codes
     *
     * @var string[]
     */
    private const ISO_COUNTRIES = [
        'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB',
        'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW',
        'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CW',
        'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ',
        'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR',
        'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ',
        'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ',
        'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH',
        'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC',
        'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL',
        'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE',
        'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD',
        'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US',
        'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW',
    ];

    /**
     * This code is the only one used by SWIFT that is not defined by ISO 3166-1 alpha-2
     * @see https://en.wikipedia.org/wiki/ISO_9362
     *
     * @var string
     */
    private const KOSOVO_EXCEPTION = 'XK';

    /**
     * @inheritDoc
     */
    public function isValid($value) : bool
    {
        if (! is_string($value)) {
            $this->error(self::NOT_STRING);
            return false;
        }

        if (empty($value)
            || ! preg_match(self::REGEX_BIC, $value, $matches)
        ) {
            $this->error(self::INVALID);
            return false;
        }

        if (! $this->isSwiftValidCountry($matches['country'])) {
            $this->error(self::NOT_VALID_COUNTRY);
            return false;
        }

        return true;
    }

    private function isSwiftValidCountry(string $countryCode) : bool
    {
        $countryCode = strtoupper($countryCode);
        return in_array($countryCode, self::ISO_COUNTRIES, true)
            || $countryCode === self::KOSOVO_EXCEPTION;
    }
}
