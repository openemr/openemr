<?php

namespace OpenEMR\FHIR\R4\FHIRCodePrimitive;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: September 10th, 2022 20:42+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRCodePrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Class FHIRSPDXLicenseList
 * @package \OpenEMR\FHIR\R4\FHIRCodePrimitive
 */
class FHIRSPDXLicenseList extends FHIRCodePrimitive
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_SPDXLICENSE_HYPHEN_LIST;

    /** @var string */
    private $_xmlns = '';

    /**
     * Validation map for fields in type SPDXLicense-list
     * @var array
     */
    private static $_validationRules = [
        self::FIELD_VALUE => [
            PHPFHIRConstants::VALIDATE_ENUM => ['not-open-source','0BSD','AAL','Abstyles','Adobe-2006','Adobe-Glyph','ADSL','AFL-1.1','AFL-1.2','AFL-2.0','AFL-2.1','AFL-3.0','Afmparse','AGPL-1.0-only','AGPL-1.0-or-later','AGPL-3.0-only','AGPL-3.0-or-later','Aladdin','AMDPLPA','AML','AMPAS','ANTLR-PD','Apache-1.0','Apache-1.1','Apache-2.0','APAFML','APL-1.0','APSL-1.0','APSL-1.1','APSL-1.2','APSL-2.0','Artistic-1.0-cl8','Artistic-1.0-Perl','Artistic-1.0','Artistic-2.0','Bahyph','Barr','Beerware','BitTorrent-1.0','BitTorrent-1.1','Borceux','BSD-1-Clause','BSD-2-Clause-FreeBSD','BSD-2-Clause-NetBSD','BSD-2-Clause-Patent','BSD-2-Clause','BSD-3-Clause-Attribution','BSD-3-Clause-Clear','BSD-3-Clause-LBNL','BSD-3-Clause-No-Nuclear-License-2014','BSD-3-Clause-No-Nuclear-License','BSD-3-Clause-No-Nuclear-Warranty','BSD-3-Clause','BSD-4-Clause-UC','BSD-4-Clause','BSD-Protection','BSD-Source-Code','BSL-1.0','bzip2-1.0.5','bzip2-1.0.6','Caldera','CATOSL-1.1','CC-BY-1.0','CC-BY-2.0','CC-BY-2.5','CC-BY-3.0','CC-BY-4.0','CC-BY-NC-1.0','CC-BY-NC-2.0','CC-BY-NC-2.5','CC-BY-NC-3.0','CC-BY-NC-4.0','CC-BY-NC-ND-1.0','CC-BY-NC-ND-2.0','CC-BY-NC-ND-2.5','CC-BY-NC-ND-3.0','CC-BY-NC-ND-4.0','CC-BY-NC-SA-1.0','CC-BY-NC-SA-2.0','CC-BY-NC-SA-2.5','CC-BY-NC-SA-3.0','CC-BY-NC-SA-4.0','CC-BY-ND-1.0','CC-BY-ND-2.0','CC-BY-ND-2.5','CC-BY-ND-3.0','CC-BY-ND-4.0','CC-BY-SA-1.0','CC-BY-SA-2.0','CC-BY-SA-2.5','CC-BY-SA-3.0','CC-BY-SA-4.0','CC0-1.0','CDDL-1.0','CDDL-1.1','CDLA-Permissive-1.0','CDLA-Sharing-1.0','CECILL-1.0','CECILL-1.1','CECILL-2.0','CECILL-2.1','CECILL-B','CECILL-C','ClArtistic','CNRI-Jython','CNRI-Python-GPL-Compatible','CNRI-Python','Condor-1.1','CPAL-1.0','CPL-1.0','CPOL-1.02','Crossword','CrystalStacker','CUA-OPL-1.0','Cube','curl','D-FSL-1.0','diffmark','DOC','Dotseqn','DSDP','dvipdfm','ECL-1.0','ECL-2.0','EFL-1.0','EFL-2.0','eGenix','Entessa','EPL-1.0','EPL-2.0','ErlPL-1.1','EUDatagrid','EUPL-1.0','EUPL-1.1','EUPL-1.2','Eurosym','Fair','Frameworx-1.0','FreeImage','FSFAP','FSFUL','FSFULLR','FTL','GFDL-1.1-only','GFDL-1.1-or-later','GFDL-1.2-only','GFDL-1.2-or-later','GFDL-1.3-only','GFDL-1.3-or-later','Giftware','GL2PS','Glide','Glulxe','gnuplot','GPL-1.0-only','GPL-1.0-or-later','GPL-2.0-only','GPL-2.0-or-later','GPL-3.0-only','GPL-3.0-or-later','gSOAP-1.3b','HaskellReport','HPND','IBM-pibs','ICU','IJG','ImageMagick','iMatix','Imlib2','Info-ZIP','Intel-ACPI','Intel','Interbase-1.0','IPA','IPL-1.0','ISC','JasPer-2.0','JSON','LAL-1.2','LAL-1.3','Latex2e','Leptonica','LGPL-2.0-only','LGPL-2.0-or-later','LGPL-2.1-only','LGPL-2.1-or-later','LGPL-3.0-only','LGPL-3.0-or-later','LGPLLR','Libpng','libtiff','LiLiQ-P-1.1','LiLiQ-R-1.1','LiLiQ-Rplus-1.1','Linux-OpenIB','LPL-1.0','LPL-1.02','LPPL-1.0','LPPL-1.1','LPPL-1.2','LPPL-1.3a','LPPL-1.3c','MakeIndex','MirOS','MIT-0','MIT-advertising','MIT-CMU','MIT-enna','MIT-feh','MIT','MITNFA','Motosoto','mpich2','MPL-1.0','MPL-1.1','MPL-2.0-no-copyleft-exception','MPL-2.0','MS-PL','MS-RL','MTLL','Multics','Mup','NASA-1.3','Naumen','NBPL-1.0','NCSA','Net-SNMP','NetCDF','Newsletr','NGPL','NLOD-1.0','NLPL','Nokia','NOSL','Noweb','NPL-1.0','NPL-1.1','NPOSL-3.0','NRL','NTP','OCCT-PL','OCLC-2.0','ODbL-1.0','OFL-1.0','OFL-1.1','OGTSL','OLDAP-1.1','OLDAP-1.2','OLDAP-1.3','OLDAP-1.4','OLDAP-2.0.1','OLDAP-2.0','OLDAP-2.1','OLDAP-2.2.1','OLDAP-2.2.2','OLDAP-2.2','OLDAP-2.3','OLDAP-2.4','OLDAP-2.5','OLDAP-2.6','OLDAP-2.7','OLDAP-2.8','OML','OpenSSL','OPL-1.0','OSET-PL-2.1','OSL-1.0','OSL-1.1','OSL-2.0','OSL-2.1','OSL-3.0','PDDL-1.0','PHP-3.0','PHP-3.01','Plexus','PostgreSQL','psfrag','psutils','Python-2.0','Qhull','QPL-1.0','Rdisc','RHeCos-1.1','RPL-1.1','RPL-1.5','RPSL-1.0','RSA-MD','RSCPL','Ruby','SAX-PD','Saxpath','SCEA','Sendmail','SGI-B-1.0','SGI-B-1.1','SGI-B-2.0','SimPL-2.0','SISSL-1.2','SISSL','Sleepycat','SMLNJ','SMPPL','SNIA','Spencer-86','Spencer-94','Spencer-99','SPL-1.0','SugarCRM-1.1.3','SWL','TCL','TCP-wrappers','TMate','TORQUE-1.1','TOSL','Unicode-DFS-2015','Unicode-DFS-2016','Unicode-TOU','Unlicense','UPL-1.0','Vim','VOSTROM','VSL-1.0','W3C-19980720','W3C-20150513','W3C','Watcom-1.0','Wsuipa','WTFPL','X11','Xerox','XFree86-1.1','xinetd','Xnet','xpp','XSkat','YPL-1.0','YPL-1.1','Zed','Zend-2.0','Zimbra-1.3','Zimbra-1.4','zlib-acknowledgement','Zlib','ZPL-1.1','ZPL-2.0','ZPL-2.1',],
        ],
    ];

    /**
     * FHIRSPDXLicenseList Constructor
     * @param null|string $value
     */
    public function __construct($value = null)
    {
        parent::__construct($value);
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<SPDXLicense_list{$xmlns}></SPDXLicense_list>";
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (isset($validationRules[self::FIELD_VALUE])) {
            $v = $this->getValue();
            foreach ($validationRules[self::FIELD_VALUE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CODE_HYPHEN_PRIMITIVE, self::FIELD_VALUE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VALUE])) {
                        $errs[self::FIELD_VALUE] = [];
                    }
                    $errs[self::FIELD_VALUE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRCodePrimitive\FHIRSPDXLicenseList $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRCodePrimitive\FHIRSPDXLicenseList
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRSPDXLicenseList::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRSPDXLicenseList::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRSPDXLicenseList(null);
        } elseif (!is_object($type) || !($type instanceof FHIRSPDXLicenseList)) {
            throw new \RuntimeException(sprintf(
                'FHIRSPDXLicenseList::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRCodePrimitive\FHIRSPDXLicenseList or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_VALUE === $n->nodeName) {
                $valueAttr = $n->attributes->getNamedItem('value');
                if (null !== $valueAttr) {
                    $type->setValue($valueAttr->nodeValue);
                } elseif ($n->hasChildNodes()) {
                    $type->setValue($n->ownerDocument->saveXML($n));
                } else {
                    $type->setValue($n->textContent);
                }
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_VALUE);
        if (null !== $n) {
            $type->setValue($n->nodeValue);
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        return $element;
    }
}
