<?php

/**
 * Builds small 835 files for the unattended posting tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

final class Era835
{
    /**
     * One transaction set (check).
     *
     * @param list<array{string, string, string}> $claims our claim id, claim status, amount paid; each has one 99213 line billed 100.00
     * @param list<string> $plb PLB03/04 pairs as "reason*amount"
     */
    public static function check(string $number, string $amount, array $claims, array $plb = [], string $control = '0001'): string
    {
        $segments = [
            "ST*835*$control",
            "BPR*I*$amount*C*ACH************20261020",
            "TRN*1*$number*1234567890",
            'DTM*405*20261019',
            'N1*PR*TEST PAYER',
            'REF*2U*60054',
            'N1*PE*TEST PROVIDER*XX*1234567890',
        ];
        if ($claims !== []) {
            $segments[] = 'LX*1';
        }
        foreach ($claims as [$claimId, $status, $paid]) {
            $segments[] = "CLP*$claimId*$status*100.00*$paid*0*MC*ICN$claimId";
            $segments[] = 'NM1*QC*1*POSTING*ANNE****MI*123456789';
            $segments[] = "SVC*HC:99213*100.00*$paid";
            $segments[] = 'DTM*472*20260915';
        }
        if ($plb !== []) {
            $segments[] = 'PLB*1234567890*20261231*' . implode('*', $plb);
        }
        $segments[] = 'SE*' . (count($segments) + 1) . "*$control";
        return implode("~\n", $segments) . "~\n";
    }

    public static function file(string ...$checks): string
    {
        return "ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *261020*1200*^*00501*000000042*0*P*:~\n"
            . "GS*HP*PAYER*RECEIVER*20261020*1200*1*X*005010X221A1~\n"
            . implode('', $checks)
            . "GE*" . count($checks) . "*1~\nIEA*1*000000042~\n";
    }

    public static function write(string $directory, string $name, string $content): string
    {
        $path = $directory . '/' . $name;
        file_put_contents($path, $content);
        return $path;
    }
}
