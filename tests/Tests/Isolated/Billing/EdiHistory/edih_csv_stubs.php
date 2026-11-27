<?php

/**
 * The two library helpers edih_277_csv_data() calls, so it can be tested without the
 * EDI History bootstrap, which needs a site directory and a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (!function_exists('csv_edihist_log')) {
    function csv_edihist_log($msg_str)
    {
        $GLOBALS['edih_test_log'][] = $msg_str;
        return 0;
    }
}

if (!function_exists('csv_file_type')) {
    function csv_file_type(string $type, bool $gs_code = false): string
    {
        if ($type === '' || $type === '0') {
            return '';
        }
        $map = [
            ['|f837|batch|HC', 'HC', 'f837'],
            ['|f835|era|HP', 'HP', 'f835'],
            ['|f999|f997|ack|ta1|FA', 'FA', 'f997'],
            ['|f277|HN', 'HN', 'f277'],
            ['|f276|HR', 'HR', 'f276'],
            ['|f271|HB', 'HB', 'f271'],
            ['|f270|HS', 'HS', 'f270'],
            ['|f278|HI', 'HI', 'f278'],
        ];
        foreach ($map as [$aliases, $gs, $ft]) {
            if (str_contains($aliases, $type)) {
                return $gs_code ? $gs : $ft;
            }
        }
        return '';
    }
}