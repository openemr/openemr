<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;

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
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Validation\Rules\ValueOneOfRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSPDXLicenseList extends FHIRCodePrimitive
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SPDXLICENSE_HYPHEN_LIST;

    /* class_default.php:56 */

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_VALUE => [
            ValueOneOfRule::NAME => [
                0 => 'not-open-source',
                1 => '0BSD',
                2 => 'AAL',
                3 => 'Abstyles',
                4 => 'Adobe-2006',
                5 => 'Adobe-Glyph',
                6 => 'ADSL',
                7 => 'AFL-1.1',
                8 => 'AFL-1.2',
                9 => 'AFL-2.0',
                10 => 'AFL-2.1',
                11 => 'AFL-3.0',
                12 => 'Afmparse',
                13 => 'AGPL-1.0-only',
                14 => 'AGPL-1.0-or-later',
                15 => 'AGPL-3.0-only',
                16 => 'AGPL-3.0-or-later',
                17 => 'Aladdin',
                18 => 'AMDPLPA',
                19 => 'AML',
                20 => 'AMPAS',
                21 => 'ANTLR-PD',
                22 => 'Apache-1.0',
                23 => 'Apache-1.1',
                24 => 'Apache-2.0',
                25 => 'APAFML',
                26 => 'APL-1.0',
                27 => 'APSL-1.0',
                28 => 'APSL-1.1',
                29 => 'APSL-1.2',
                30 => 'APSL-2.0',
                31 => 'Artistic-1.0-cl8',
                32 => 'Artistic-1.0-Perl',
                33 => 'Artistic-1.0',
                34 => 'Artistic-2.0',
                35 => 'Bahyph',
                36 => 'Barr',
                37 => 'Beerware',
                38 => 'BitTorrent-1.0',
                39 => 'BitTorrent-1.1',
                40 => 'Borceux',
                41 => 'BSD-1-Clause',
                42 => 'BSD-2-Clause-FreeBSD',
                43 => 'BSD-2-Clause-NetBSD',
                44 => 'BSD-2-Clause-Patent',
                45 => 'BSD-2-Clause',
                46 => 'BSD-3-Clause-Attribution',
                47 => 'BSD-3-Clause-Clear',
                48 => 'BSD-3-Clause-LBNL',
                49 => 'BSD-3-Clause-No-Nuclear-License-2014',
                50 => 'BSD-3-Clause-No-Nuclear-License',
                51 => 'BSD-3-Clause-No-Nuclear-Warranty',
                52 => 'BSD-3-Clause',
                53 => 'BSD-4-Clause-UC',
                54 => 'BSD-4-Clause',
                55 => 'BSD-Protection',
                56 => 'BSD-Source-Code',
                57 => 'BSL-1.0',
                58 => 'bzip2-1.0.5',
                59 => 'bzip2-1.0.6',
                60 => 'Caldera',
                61 => 'CATOSL-1.1',
                62 => 'CC-BY-1.0',
                63 => 'CC-BY-2.0',
                64 => 'CC-BY-2.5',
                65 => 'CC-BY-3.0',
                66 => 'CC-BY-4.0',
                67 => 'CC-BY-NC-1.0',
                68 => 'CC-BY-NC-2.0',
                69 => 'CC-BY-NC-2.5',
                70 => 'CC-BY-NC-3.0',
                71 => 'CC-BY-NC-4.0',
                72 => 'CC-BY-NC-ND-1.0',
                73 => 'CC-BY-NC-ND-2.0',
                74 => 'CC-BY-NC-ND-2.5',
                75 => 'CC-BY-NC-ND-3.0',
                76 => 'CC-BY-NC-ND-4.0',
                77 => 'CC-BY-NC-SA-1.0',
                78 => 'CC-BY-NC-SA-2.0',
                79 => 'CC-BY-NC-SA-2.5',
                80 => 'CC-BY-NC-SA-3.0',
                81 => 'CC-BY-NC-SA-4.0',
                82 => 'CC-BY-ND-1.0',
                83 => 'CC-BY-ND-2.0',
                84 => 'CC-BY-ND-2.5',
                85 => 'CC-BY-ND-3.0',
                86 => 'CC-BY-ND-4.0',
                87 => 'CC-BY-SA-1.0',
                88 => 'CC-BY-SA-2.0',
                89 => 'CC-BY-SA-2.5',
                90 => 'CC-BY-SA-3.0',
                91 => 'CC-BY-SA-4.0',
                92 => 'CC0-1.0',
                93 => 'CDDL-1.0',
                94 => 'CDDL-1.1',
                95 => 'CDLA-Permissive-1.0',
                96 => 'CDLA-Sharing-1.0',
                97 => 'CECILL-1.0',
                98 => 'CECILL-1.1',
                99 => 'CECILL-2.0',
                100 => 'CECILL-2.1',
                101 => 'CECILL-B',
                102 => 'CECILL-C',
                103 => 'ClArtistic',
                104 => 'CNRI-Jython',
                105 => 'CNRI-Python-GPL-Compatible',
                106 => 'CNRI-Python',
                107 => 'Condor-1.1',
                108 => 'CPAL-1.0',
                109 => 'CPL-1.0',
                110 => 'CPOL-1.02',
                111 => 'Crossword',
                112 => 'CrystalStacker',
                113 => 'CUA-OPL-1.0',
                114 => 'Cube',
                115 => 'curl',
                116 => 'D-FSL-1.0',
                117 => 'diffmark',
                118 => 'DOC',
                119 => 'Dotseqn',
                120 => 'DSDP',
                121 => 'dvipdfm',
                122 => 'ECL-1.0',
                123 => 'ECL-2.0',
                124 => 'EFL-1.0',
                125 => 'EFL-2.0',
                126 => 'eGenix',
                127 => 'Entessa',
                128 => 'EPL-1.0',
                129 => 'EPL-2.0',
                130 => 'ErlPL-1.1',
                131 => 'EUDatagrid',
                132 => 'EUPL-1.0',
                133 => 'EUPL-1.1',
                134 => 'EUPL-1.2',
                135 => 'Eurosym',
                136 => 'Fair',
                137 => 'Frameworx-1.0',
                138 => 'FreeImage',
                139 => 'FSFAP',
                140 => 'FSFUL',
                141 => 'FSFULLR',
                142 => 'FTL',
                143 => 'GFDL-1.1-only',
                144 => 'GFDL-1.1-or-later',
                145 => 'GFDL-1.2-only',
                146 => 'GFDL-1.2-or-later',
                147 => 'GFDL-1.3-only',
                148 => 'GFDL-1.3-or-later',
                149 => 'Giftware',
                150 => 'GL2PS',
                151 => 'Glide',
                152 => 'Glulxe',
                153 => 'gnuplot',
                154 => 'GPL-1.0-only',
                155 => 'GPL-1.0-or-later',
                156 => 'GPL-2.0-only',
                157 => 'GPL-2.0-or-later',
                158 => 'GPL-3.0-only',
                159 => 'GPL-3.0-or-later',
                160 => 'gSOAP-1.3b',
                161 => 'HaskellReport',
                162 => 'HPND',
                163 => 'IBM-pibs',
                164 => 'ICU',
                165 => 'IJG',
                166 => 'ImageMagick',
                167 => 'iMatix',
                168 => 'Imlib2',
                169 => 'Info-ZIP',
                170 => 'Intel-ACPI',
                171 => 'Intel',
                172 => 'Interbase-1.0',
                173 => 'IPA',
                174 => 'IPL-1.0',
                175 => 'ISC',
                176 => 'JasPer-2.0',
                177 => 'JSON',
                178 => 'LAL-1.2',
                179 => 'LAL-1.3',
                180 => 'Latex2e',
                181 => 'Leptonica',
                182 => 'LGPL-2.0-only',
                183 => 'LGPL-2.0-or-later',
                184 => 'LGPL-2.1-only',
                185 => 'LGPL-2.1-or-later',
                186 => 'LGPL-3.0-only',
                187 => 'LGPL-3.0-or-later',
                188 => 'LGPLLR',
                189 => 'Libpng',
                190 => 'libtiff',
                191 => 'LiLiQ-P-1.1',
                192 => 'LiLiQ-R-1.1',
                193 => 'LiLiQ-Rplus-1.1',
                194 => 'Linux-OpenIB',
                195 => 'LPL-1.0',
                196 => 'LPL-1.02',
                197 => 'LPPL-1.0',
                198 => 'LPPL-1.1',
                199 => 'LPPL-1.2',
                200 => 'LPPL-1.3a',
                201 => 'LPPL-1.3c',
                202 => 'MakeIndex',
                203 => 'MirOS',
                204 => 'MIT-0',
                205 => 'MIT-advertising',
                206 => 'MIT-CMU',
                207 => 'MIT-enna',
                208 => 'MIT-feh',
                209 => 'MIT',
                210 => 'MITNFA',
                211 => 'Motosoto',
                212 => 'mpich2',
                213 => 'MPL-1.0',
                214 => 'MPL-1.1',
                215 => 'MPL-2.0-no-copyleft-exception',
                216 => 'MPL-2.0',
                217 => 'MS-PL',
                218 => 'MS-RL',
                219 => 'MTLL',
                220 => 'Multics',
                221 => 'Mup',
                222 => 'NASA-1.3',
                223 => 'Naumen',
                224 => 'NBPL-1.0',
                225 => 'NCSA',
                226 => 'Net-SNMP',
                227 => 'NetCDF',
                228 => 'Newsletr',
                229 => 'NGPL',
                230 => 'NLOD-1.0',
                231 => 'NLPL',
                232 => 'Nokia',
                233 => 'NOSL',
                234 => 'Noweb',
                235 => 'NPL-1.0',
                236 => 'NPL-1.1',
                237 => 'NPOSL-3.0',
                238 => 'NRL',
                239 => 'NTP',
                240 => 'OCCT-PL',
                241 => 'OCLC-2.0',
                242 => 'ODbL-1.0',
                243 => 'OFL-1.0',
                244 => 'OFL-1.1',
                245 => 'OGTSL',
                246 => 'OLDAP-1.1',
                247 => 'OLDAP-1.2',
                248 => 'OLDAP-1.3',
                249 => 'OLDAP-1.4',
                250 => 'OLDAP-2.0.1',
                251 => 'OLDAP-2.0',
                252 => 'OLDAP-2.1',
                253 => 'OLDAP-2.2.1',
                254 => 'OLDAP-2.2.2',
                255 => 'OLDAP-2.2',
                256 => 'OLDAP-2.3',
                257 => 'OLDAP-2.4',
                258 => 'OLDAP-2.5',
                259 => 'OLDAP-2.6',
                260 => 'OLDAP-2.7',
                261 => 'OLDAP-2.8',
                262 => 'OML',
                263 => 'OpenSSL',
                264 => 'OPL-1.0',
                265 => 'OSET-PL-2.1',
                266 => 'OSL-1.0',
                267 => 'OSL-1.1',
                268 => 'OSL-2.0',
                269 => 'OSL-2.1',
                270 => 'OSL-3.0',
                271 => 'PDDL-1.0',
                272 => 'PHP-3.0',
                273 => 'PHP-3.01',
                274 => 'Plexus',
                275 => 'PostgreSQL',
                276 => 'psfrag',
                277 => 'psutils',
                278 => 'Python-2.0',
                279 => 'Qhull',
                280 => 'QPL-1.0',
                281 => 'Rdisc',
                282 => 'RHeCos-1.1',
                283 => 'RPL-1.1',
                284 => 'RPL-1.5',
                285 => 'RPSL-1.0',
                286 => 'RSA-MD',
                287 => 'RSCPL',
                288 => 'Ruby',
                289 => 'SAX-PD',
                290 => 'Saxpath',
                291 => 'SCEA',
                292 => 'Sendmail',
                293 => 'SGI-B-1.0',
                294 => 'SGI-B-1.1',
                295 => 'SGI-B-2.0',
                296 => 'SimPL-2.0',
                297 => 'SISSL-1.2',
                298 => 'SISSL',
                299 => 'Sleepycat',
                300 => 'SMLNJ',
                301 => 'SMPPL',
                302 => 'SNIA',
                303 => 'Spencer-86',
                304 => 'Spencer-94',
                305 => 'Spencer-99',
                306 => 'SPL-1.0',
                307 => 'SugarCRM-1.1.3',
                308 => 'SWL',
                309 => 'TCL',
                310 => 'TCP-wrappers',
                311 => 'TMate',
                312 => 'TORQUE-1.1',
                313 => 'TOSL',
                314 => 'Unicode-DFS-2015',
                315 => 'Unicode-DFS-2016',
                316 => 'Unicode-TOU',
                317 => 'Unlicense',
                318 => 'UPL-1.0',
                319 => 'Vim',
                320 => 'VOSTROM',
                321 => 'VSL-1.0',
                322 => 'W3C-19980720',
                323 => 'W3C-20150513',
                324 => 'W3C',
                325 => 'Watcom-1.0',
                326 => 'Wsuipa',
                327 => 'WTFPL',
                328 => 'X11',
                329 => 'Xerox',
                330 => 'XFree86-1.1',
                331 => 'xinetd',
                332 => 'Xnet',
                333 => 'xpp',
                334 => 'XSkat',
                335 => 'YPL-1.0',
                336 => 'YPL-1.1',
                337 => 'Zed',
                338 => 'Zend-2.0',
                339 => 'Zimbra-1.3',
                340 => 'Zimbra-1.4',
                341 => 'zlib-acknowledgement',
                342 => 'Zlib',
                343 => 'ZPL-1.1',
                344 => 'ZPL-2.0',
                345 => 'ZPL-2.1',
            ],
        ],
    ];

    /* class_default.php:112 */

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:201 */
}
