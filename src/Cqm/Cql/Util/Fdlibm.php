<?php

/**
 * Math.pow, Math.log and Math.exp exactly as V8 computes them: ports of
 * fdlibm's e_pow.c, e_log.c and e_exp.c as V8 12 adapted them in
 * src/base/ieee754.cc. These are not correctly rounded (V8's
 * Math.pow(10, -4) is 0.00009999999999999999), and UCUM magnitudes built
 * from them differ in the last place from PHP's pow, log and exp; the
 * ports keep unit conversions identical to cql-execution's.
 *
 * fdlibm notice:
 * ====================================================
 * Copyright (C) 1993 by Sun Microsystems, Inc. All rights reserved.
 *
 * Developed at SunSoft, a Sun Microsystems, Inc. business.
 * Permission to use, copy, modify, and distribute this
 * software is freely granted, provided that this notice
 * is preserved.
 * ====================================================
 *
 * V8 notice (src/base/ieee754.cc):
 * The original source code covered by the above license above has been
 * modified significantly by Google Inc.
 * Copyright 2016 the V8 project authors. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials provided
 *       with the distribution.
 *     * Neither the name of Google Inc. nor the names of its
 *       contributors may be used to endorse or promote products derived
 *       from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Util;

final class Fdlibm
{
    private const BP = [1.0, 1.5];
    private const DP_H = [0.0, 5.84962487220764160156e-01];
    private const DP_L = [0.0, 1.35003920212974897128e-08];
    private const TWO53 = 9007199254740992.0;
    private const HUGE = 1.0e300;
    private const TINY = 1.0e-300;
    private const L1 = 5.99999999999994648725e-01;
    private const L2 = 4.28571428578550184252e-01;
    private const L3 = 3.33333329818377432918e-01;
    private const L4 = 2.72728123808534006489e-01;
    private const L5 = 2.30660745775561754067e-01;
    private const L6 = 2.06975017800338417784e-01;
    private const P1 = 1.66666666666666019037e-01;
    private const P2 = -2.77777777770155933842e-03;
    private const P3 = 6.61375632143793436117e-05;
    private const P4 = -1.65339022054652515390e-06;
    private const P5 = 4.13813679705723846039e-08;
    private const LG2 = 6.93147180559945286227e-01;
    private const LG2_H = 6.93147182464599609375e-01;
    private const LG2_L = -1.90465429995776804525e-09;
    private const OVT = 8.0085662595372944372e-17;
    private const CP = 9.61796693925975554329e-01;
    private const CP_H = 9.61796700954437255859e-01;
    private const CP_L = -7.02846165095275826516e-09;
    private const IVLN2 = 1.44269504088896338700e+00;
    private const IVLN2_H = 1.44269502162933349609e+00;
    private const IVLN2_L = 1.92596299112661746887e-08;
    private const LN2_HI = 6.93147180369123816490e-01;
    private const LN2_LO = 1.90821492927058770002e-10;

    /** V8's Math.pow. */
    public static function pow(float $x, float $y): float
    {
        [$hx, $lx] = self::words($x);
        [$hy, $ly] = self::words($y);
        $ix = $hx & 0x7fffffff;
        $iy = $hy & 0x7fffffff;

        // y == 0: x**0 = 1
        if (($iy | $ly) === 0) {
            return 1.0;
        }
        // NaN in, NaN out
        if ($ix > 0x7ff00000 || ($ix === 0x7ff00000 && $lx !== 0) || $iy > 0x7ff00000 || ($iy === 0x7ff00000 && $ly !== 0)) {
            return $x + $y;
        }

        // Whether y is an odd (1) or even (2) integer when x < 0.
        $yisint = 0;
        if ($hx < 0) {
            if ($iy >= 0x43400000) {
                $yisint = 2;
            } elseif ($iy >= 0x3ff00000) {
                $k = ($iy >> 20) - 0x3ff;
                if ($k > 20) {
                    $j = $ly >> (52 - $k);
                    if (self::int32($j << (52 - $k)) === self::int32($ly)) {
                        $yisint = 2 - ($j & 1);
                    }
                } elseif ($ly === 0) {
                    $j = $iy >> (20 - $k);
                    if (($j << (20 - $k)) === $iy) {
                        $yisint = 2 - ($j & 1);
                    }
                }
            }
        }

        // Special values of y.
        if ($ly === 0) {
            if ($iy === 0x7ff00000) {
                if ((($ix - 0x3ff00000) | $lx) === 0) {
                    return NAN;
                }
                if ($ix >= 0x3ff00000) {
                    return $hy >= 0 ? $y : 0.0;
                }
                return $hy < 0 ? -$y : 0.0;
            }
            if ($iy === 0x3ff00000) {
                return $hy < 0 ? fdiv(1.0, $x) : $x;
            }
            if ($hy === 0x40000000) {
                return $x * $x;
            }
            if ($hy === 0x3fe00000 && $hx >= 0) {
                return sqrt($x);
            }
        }

        $ax = abs($x);
        // Special values of x: +-0, +-inf, +-1.
        if ($lx === 0 && ($ix === 0x7ff00000 || $ix === 0 || $ix === 0x3ff00000)) {
            $z = $ax;
            if ($hy < 0) {
                $z = fdiv(1.0, $z);
            }
            if ($hx < 0) {
                if ((($ix - 0x3ff00000) | $yisint) === 0) {
                    $z = NAN;
                } elseif ($yisint === 1) {
                    $z = -$z;
                }
            }
            return $z;
        }

        $n = ($hx >> 31) + 1;
        // (x<0)**(non-int) is NaN
        if (($n | $yisint) === 0) {
            return NAN;
        }
        $s = ($n | ($yisint - 1)) === 0 ? -1.0 : 1.0;

        if ($iy > 0x41e00000) {
            // |y| > 2**31
            if ($iy > 0x43f00000) {
                if ($ix <= 0x3fefffff) {
                    return $hy < 0 ? self::HUGE * self::HUGE : self::TINY * self::TINY;
                }
                return $hy > 0 ? self::HUGE * self::HUGE : self::TINY * self::TINY;
            }
            if ($ix < 0x3fefffff) {
                return $hy < 0 ? $s * self::HUGE * self::HUGE : $s * self::TINY * self::TINY;
            }
            if ($ix > 0x3ff00000) {
                return $hy > 0 ? $s * self::HUGE * self::HUGE : $s * self::TINY * self::TINY;
            }
            $t = $ax - 1.0;
            $w = ($t * $t) * (0.5 - $t * (0.3333333333333333333333 - $t * 0.25));
            $u = self::IVLN2_H * $t;
            $v = $t * self::IVLN2_L - $w * self::IVLN2;
            $t1 = self::withLowWord($u + $v, 0);
            $t2 = $v - ($t1 - $u);
        } else {
            $n = 0;
            if ($ix < 0x00100000) {
                // subnormal x
                $ax *= self::TWO53;
                $n -= 53;
                $ix = self::words($ax)[0];
            }
            $n += ($ix >> 20) - 0x3ff;
            $j = $ix & 0x000fffff;
            $ix = $j | 0x3ff00000;
            if ($j <= 0x3988E) {
                $k = 0;
            } elseif ($j < 0xBB67A) {
                $k = 1;
            } else {
                $k = 0;
                $n += 1;
                $ix -= 0x00100000;
            }
            $ax = self::withHighWord($ax, $ix);

            // ss = s_h + s_l = (x-1)/(x+1) or (x-1.5)/(x+1.5)
            $u = $ax - self::BP[$k];
            $v = fdiv(1.0, $ax + self::BP[$k]);
            $ss = $u * $v;
            $sH = self::withLowWord($ss, 0);
            $tH = self::fromWords((($ix >> 1) | 0x20000000) + 0x00080000 + ($k << 18), 0);
            $tL = $ax - ($tH - self::BP[$k]);
            $sL = $v * (($u - $sH * $tH) - $sH * $tL);
            // log(ax)
            $s2 = $ss * $ss;
            $r = $s2 * $s2 * (self::L1 + $s2 * (self::L2 + $s2 * (self::L3 + $s2 * (self::L4 + $s2 * (self::L5 + $s2 * self::L6)))));
            $r += $sL * ($sH + $ss);
            $s2 = $sH * $sH;
            $tH = self::withLowWord(3.0 + $s2 + $r, 0);
            $tL = $r - (($tH - 3.0) - $s2);
            $u = $sH * $tH;
            $v = $sL * $tH + $tL * $ss;
            $pH = self::withLowWord($u + $v, 0);
            $pL = $v - ($pH - $u);
            $zH = self::CP_H * $pH;
            $zL = self::CP_L * $pH + $pL * self::CP + self::DP_L[$k];
            $t = (float) $n;
            $t1 = self::withLowWord((($zH + $zL) + self::DP_H[$k]) + $t, 0);
            $t2 = $zL - ((($t1 - $t) - self::DP_H[$k]) - $zH);
        }

        // (y1 + y2) * (t1 + t2)
        $y1 = self::withLowWord($y, 0);
        $pL = ($y - $y1) * $t1 + $y * $t2;
        $pH = $y1 * $t1;
        $z = $pL + $pH;
        [$j, $i] = self::words($z);
        if ($j >= 0x40900000) {
            // z >= 1024
            if ((($j - 0x40900000) | $i) !== 0) {
                return $s * self::HUGE * self::HUGE;
            }
            if ($pL + self::OVT > $z - $pH) {
                return $s * self::HUGE * self::HUGE;
            }
        } elseif (($j & 0x7fffffff) >= 0x4090cc00) {
            // z <= -1075
            if (self::int32($j - self::int32(0xc090cc00)) !== 0 || $i !== 0) {
                return $s * self::TINY * self::TINY;
            }
            if ($pL <= $z - $pH) {
                return $s * self::TINY * self::TINY;
            }
        }

        // 2**(p_h + p_l)
        $i = $j & 0x7fffffff;
        $k = ($i >> 20) - 0x3ff;
        $n = 0;
        if ($i > 0x3fe00000) {
            $n = $j + (0x00100000 >> ($k + 1));
            $k = (($n & 0x7fffffff) >> 20) - 0x3ff;
            $t = self::fromWords($n & ~(0x000fffff >> $k), 0);
            $n = (($n & 0x000fffff) | 0x00100000) >> (20 - $k);
            if ($j < 0) {
                $n = -$n;
            }
            $pH -= $t;
        }
        $t = self::withLowWord($pL + $pH, 0);
        $u = $t * self::LG2_H;
        $v = ($pL - ($t - $pH)) * self::LG2 + $t * self::LG2_L;
        $z = $u + $v;
        $w = $v - ($z - $u);
        $t = $z * $z;
        $t1 = $z - $t * (self::P1 + $t * (self::P2 + $t * (self::P3 + $t * (self::P4 + $t * self::P5))));
        $r = fdiv($z * $t1, ($t1 - 2.0) - ($w + $z * $w));
        $z = 1.0 - ($r - $z);
        $j = self::words($z)[0];
        $j += self::int32(($n & 0xffffffff) << 20);
        if (($j >> 20) <= 0) {
            $z = self::scalbn($z, $n);
        } else {
            $z = self::withHighWord($z, $j);
        }
        return $s * $z;
    }

    /** V8's Math.log. */
    public static function log(float $x): float
    {
        $lg1 = 6.666666666666735130e-01;
        $lg2 = 3.999999999940941908e-01;
        $lg3 = 2.857142874366239149e-01;
        $lg4 = 2.222219843214978396e-01;
        $lg5 = 1.818357216161805012e-01;
        $lg6 = 1.531383769920937332e-01;
        $lg7 = 1.479819860511658591e-01;

        [$hx, $lx] = self::words($x);
        $k = 0;
        if ($hx < 0x00100000) {
            if ((($hx & 0x7fffffff) | $lx) === 0) {
                return -INF;
            }
            if ($hx < 0) {
                return NAN;
            }
            $k -= 54;
            $x *= 1.80143985094819840000e+16;
            $hx = self::words($x)[0];
        }
        if ($hx >= 0x7ff00000) {
            return $x + $x;
        }
        $k += ($hx >> 20) - 1023;
        $hx &= 0x000fffff;
        $i = ($hx + 0x95F64) & 0x100000;
        $x = self::withHighWord($x, $hx | ($i ^ 0x3ff00000));
        $k += ($i >> 20);
        $f = $x - 1.0;
        if ((0x000fffff & (2 + $hx)) < 3) {
            // -2**-20 <= f < 2**-20
            if ($f == 0.0) {
                if ($k === 0) {
                    return 0.0;
                }
                $dk = (float) $k;
                return $dk * self::LN2_HI + $dk * self::LN2_LO;
            }
            $r = $f * $f * (0.5 - 0.33333333333333333 * $f);
            if ($k === 0) {
                return $f - $r;
            }
            $dk = (float) $k;
            return $dk * self::LN2_HI - (($r - $dk * self::LN2_LO) - $f);
        }
        $s = $f / (2.0 + $f);
        $dk = (float) $k;
        $z = $s * $s;
        $i = $hx - 0x6147A;
        $w = $z * $z;
        $j = 0x6B851 - $hx;
        $t1 = $w * ($lg2 + $w * ($lg4 + $w * $lg6));
        $t2 = $z * ($lg1 + $w * ($lg3 + $w * ($lg5 + $w * $lg7)));
        $i |= $j;
        $r = $t2 + $t1;
        if ($i > 0) {
            $hfsq = 0.5 * $f * $f;
            if ($k === 0) {
                return $f - ($hfsq - $s * ($hfsq + $r));
            }
            return $dk * self::LN2_HI - (($hfsq - ($s * ($hfsq + $r) + $dk * self::LN2_LO)) - $f);
        }
        if ($k === 0) {
            return $f - $s * ($f - $r);
        }
        return $dk * self::LN2_HI - (($s * ($f - $r) - $dk * self::LN2_LO) - $f);
    }

    /** V8's Math.exp. */
    public static function exp(float $x): float
    {
        $oThreshold = 7.09782712893383973096e+02;
        $uThreshold = -7.45133219101941108420e+02;
        $twom1000 = 9.33263618503218878990e-302;
        $two1023 = 8.988465674311579539e307;

        $hx = self::words($x)[0] & 0xffffffff;
        $xsb = ($hx >> 31) & 1;
        $hx &= 0x7fffffff;
        $hi = 0.0;
        $lo = 0.0;
        $k = 0;

        if ($hx >= 0x40862E42) {
            if ($hx >= 0x7ff00000) {
                $lx = self::words($x)[1];
                if ((($hx & 0xfffff) | $lx) !== 0) {
                    return $x + $x;
                }
                return $xsb === 0 ? $x : 0.0;
            }
            if ($x > $oThreshold) {
                return INF;
            }
            if ($x < $uThreshold) {
                return 0.0;
            }
        }

        if ($hx > 0x3FD62E42) {
            if ($hx < 0x3FF0A2B2) {
                if ($x == 1.0) {
                    return 2.718281828459045;
                }
                $hi = $x - ($xsb === 0 ? self::LN2_HI : -self::LN2_HI);
                $lo = $xsb === 0 ? self::LN2_LO : -self::LN2_LO;
                $k = 1 - $xsb - $xsb;
            } else {
                $k = (int) (self::IVLN2 * $x + ($xsb === 0 ? 0.5 : -0.5));
                $t = (float) $k;
                $hi = $x - $t * self::LN2_HI;
                $lo = $t * self::LN2_LO;
            }
            $x = $hi - $lo;
        } elseif ($hx < 0x3E300000) {
            return 1.0 + $x;
        }

        $t = $x * $x;
        $twopk = $k >= -1021
            ? self::fromWords(0x3ff00000 + self::int32(($k & 0xffffffff) << 20), 0)
            : self::fromWords(0x3ff00000 + ((($k + 1000) & 0xffffffff) << 20), 0);
        $c = $x - $t * (self::P1 + $t * (self::P2 + $t * (self::P3 + $t * (self::P4 + $t * self::P5))));
        if ($k === 0) {
            return 1.0 - (($x * $c) / ($c - 2.0) - $x);
        }
        $y = 1.0 - (($lo - ($x * $c) / (2.0 - $c)) - $hi);
        if ($k >= -1021) {
            return $k === 1024 ? $y * 2.0 * $two1023 : $y * $twopk;
        }
        return $y * $twopk * $twom1000;
    }

    /**
     * The high word (signed) and low word (unsigned) of a double.
     *
     * @return array{int, int}
     */
    private static function words(float $x): array
    {
        $packed = pack('E', $x);
        $high = (ord($packed[0]) << 24) | (ord($packed[1]) << 16) | (ord($packed[2]) << 8) | ord($packed[3]);
        $low = (ord($packed[4]) << 24) | (ord($packed[5]) << 16) | (ord($packed[6]) << 8) | ord($packed[7]);
        return [self::int32($high), $low];
    }

    private static function fromWords(int $high, int $low): float
    {
        $bytes = '';
        foreach ([$high, $low] as $word) {
            $bytes .= chr(($word >> 24) & 0xff) . chr(($word >> 16) & 0xff) . chr(($word >> 8) & 0xff) . chr($word & 0xff);
        }
        $value = unpack('E', $bytes);
        return is_array($value) && is_float($value[1] ?? null) ? $value[1] : throw new \LogicException('Cannot unpack a double');
    }

    private static function withLowWord(float $x, int $low): float
    {
        return self::fromWords(self::words($x)[0], $low);
    }

    private static function withHighWord(float $x, int $high): float
    {
        return self::fromWords($high, self::words($x)[1]);
    }

    /** A value as C's 32-bit signed int holds it. */
    private static function int32(int $value): int
    {
        $value &= 0xffffffff;
        return $value >= 0x80000000 ? $value - 0x100000000 : $value;
    }

    /** x * 2**n with a single rounding, for subnormal results. */
    private static function scalbn(float $x, int $n): float
    {
        while ($n > 1023) {
            $x *= 2.0 ** 1023;
            $n -= 1023;
        }
        if ($n < -1022) {
            $x *= 2.0 ** -1022;
            $n += 1022;
            if ($n < -1022) {
                // underflows to zero either way
                return $x * 2.0 ** -1022;
            }
        }
        return $x * 2.0 ** $n;
    }
}
