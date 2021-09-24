<?xml version="1.0" encoding="UTF-8"?><!--
  Title: Lantana's CDA Stylesheet
  Original Filename: cda.xsl
  Usage: This stylesheet is designed for use with clinical documents

  Revision History: 2015-08-31 Eric Parapini - Original Commit
  Revision History: 2015-08-31 Eric Parapini - Updating Built in CSS for Camara conversion, fixed the rendering issue with Table of contents linking (Sean's help)
  Revision History: 2015-09-01 Eric Parapini - Updating Colors, Revamping the CSS, New Vision of the Header Information, Hover Tables, Formatted Patient Information Initial Release
  Revision History: 2015-09-03 Eric Parapini - Cleaned up CSS - Documentationof, added Header/Body/Footer Elements
  Revision History: 2015-10-01 Eric Parapini - CSS is now separated, Encounter of is moved down, including Bootstrap elements
  Revision History: 2015-10-02 Eric Parapini - CSS now has new styles that will take over the other spots
  Revision History: 2015-10-05 Eric Parapini - CSS updated, better use of bootstrap elements, responsive
  Revision History: 2015-10-06 Eric Parapini - Stylesheet rendering updated, Author section redone, tables now render in section elements
  Revision History: 2015-10-07 Eric Parapini - Changed the font sizes
  Revision History: 2015-10-21 Eric Parapini - Fixed logic, cleaned everything up, making the document more consistent
  Revision History: 2015-10-22 Eric Parapini - Converted some more sections to the modern bootstrap formatting, reorganized the footer
                                               Fixed up the assigned entity formatting
                                               Fixed up the informant
  Revision History: 2015-10-22 Eric Parapini - Fixed a few more things, disabled table of content generation for now
                                               Removed the timezone offset in date renderings, deemed unecessary.
  Revision History: 2015-12-10 Eric Parapini - Removed some of the additional time errors
  Revision History: 2016-02-22 Eric Parapini - Added Logo space, added in some javascript background support for interactive navigation bars
  Revision History: 2016-02-23 Eric Parapini - Added smooth scrolling, making the document easier to navigate
  Revision History: 2016-02-24 Eric Parapini - Added some CSS and content to make the table of contents styling easier to control
  Revision History: 2016-02-29 Eric Parapini - Added patient information entry in the table of contents
  Revision History: 2016-03-09 Eric Parapini - Adding in simple matches for common identifier OIDS (SSN, Driver's licenses)
                                               Additional fixes to the TOC, working on scrollspy working
                                               Fixed issue with Care = PROV not being recrognized
  Revision History: 2016-05-10 Eric Parapini - Updated Table of Contents to properly highlight location within document
  Revision History: 2016-05-17 Eric Parapini - Updated location of the next of kin to be with the patient information
  Revision History: 2016-06-08 Eric Parapini - Removed Emergency Contact Table of Contents
  Revision History: 2016-08-06 Eric Parapini - Table of Contents Drag and Drop
  Revision History: 2016-08-08 Eric Parapini - Document Type shows up in rendered view
  Revision History: 2016-11-14 Eric Parapini - Further Separating supporting libraries
  Revision History: 2017-02-09 Eric Parapini - Fixed Bug removing styleCodes
  Revision History: 2017-02-24 Eric Parapini - Fixed titles
  Revision History: 2017-02-26 Eric Parapini - Cleaned up some code
  Revision History: 2017-03-31 Eric Parapini - Whitespace issues fixing
  Revision History: 2017-04-05 Eric Parapini - Whitespace tweaking in the header, added patient ID highlighting
  Revision History: 2017-04-06 Eric Parapini - Tweaked encounter whitespace organization

  This style sheet is based on a major revision of the original CDA XSL, which was made possible thanks to the contributions of:
  - Jingdong Li
  - KH
  - Rick Geimer
  - Sean McIlvenna
  - Dale Nelson

--><!--
Copyright 2016 Lantana Consulting Group

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
--><xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <!-- This is where all the styles are loaded -->



  <xsl:output xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" method="html" indent="yes" version="4.01" encoding="UTF-8" doctype-system="http://www.w3.org/TR/html4/strict.dtd" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
  <xsl:param xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="limit-external-images" select="'yes'"/>
  <!-- A vertical bar separated list of URI prefixes, such as "http://www.example.com|https://www.example.com" -->
  <xsl:param xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="external-image-whitelist"/>
  <xsl:param xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="logo-location"/>
  <!-- string processing variables -->
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="lc" select="'abcdefghijklmnopqrstuvwxyz'"/>
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="uc" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
  <!-- removes the following characters, in addition to line breaks "':;?`{}“”„‚’ -->
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="simple-sanitizer-match">
    <xsl:text>
&#13;"':;?`{}“”„‚’</xsl:text>
  </xsl:variable>
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="simple-sanitizer-replace" select="'***************'"/>
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="javascript-injection-warning">WARNING: Javascript injection attempt detected
    in source CDA document. Terminating</xsl:variable>
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="malicious-content-warning">WARNING: Potentially malicious content found in CDA
    document.</xsl:variable>

  <!-- global variable title -->
  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="title">
    <xsl:choose>
      <xsl:when test="string-length(/n1:ClinicalDocument/n1:title) &gt;= 1">
        <xsl:value-of select="/n1:ClinicalDocument/n1:title"/>
      </xsl:when>
      <xsl:when test="/n1:ClinicalDocument/n1:code/@displayName">
        <xsl:value-of select="/n1:ClinicalDocument/n1:code/@displayName"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>Clinical Document</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>


  <!-- Main -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="/">
    <xsl:apply-templates select="n1:ClinicalDocument"/>
  </xsl:template>

  <!-- produce browser rendered, human readable clinical document -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:ClinicalDocument">
    <html>
      <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <xsl:comment> Do NOT edit this HTML directly: it was generated via an XSLT transformation from a CDA Release 2 XML document. </xsl:comment>
        <title class="cda-title">
          <xsl:value-of select="$title"/>
        </title>
        <xsl:call-template name="jquery"/>
        <xsl:call-template name="jquery-ui"/>
        <xsl:call-template name="bootstrap-css"/>
        <xsl:call-template name="bootstrap-javascript"/>
        <xsl:call-template name="lantana-js"/>
        <xsl:call-template name="lantana-css"/>
      </head>
      <body data-spy="scroll" data-target="#navbar-cda">

        <div class="cda-render toc col-md-3" role="complementary">

          <!-- produce table of contents -->
          <xsl:if test="not(//n1:nonXMLBody)">
            <xsl:if test="count(/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component[n1:section]) &gt; 0">
              <xsl:call-template name="make-tableofcontents"/>
            </xsl:if>
          </xsl:if>
        </div>

        <!-- Container: CDA Render -->
        <div class="cda-render container-fluid col-md-9 cda-render-main" role="main">

          <row>
            <h1 id="top" class="cda-title">
              <xsl:value-of select="$title"/>
            </h1>
          </row>
          <!-- START display top portion of clinical document -->
          <div class="top container-fluid">
            <xsl:call-template name="recordTarget"/>
            <xsl:call-template name="documentationOf"/>
            <xsl:call-template name="author"/>
            <xsl:call-template name="componentOf"/>
            <xsl:call-template name="participant"/>
            <xsl:call-template name="informant"/>
            <xsl:call-template name="informationRecipient"/>
            <xsl:call-template name="legalAuthenticator"/>
          </div>
          <!-- END display top portion of clinical document -->

          <!-- produce human readable document content -->
          <div class="middle" id="doc-clinical-info">
            <xsl:apply-templates select="n1:component/n1:structuredBody | n1:component/n1:nonXMLBody"/>
          </div>
          <!-- Footer -->
          <div class="bottom" id="doc-info">
            <xsl:call-template name="authenticator"/>
            <xsl:call-template name="custodian"/>
            <xsl:call-template name="dataEnterer"/>
            <xsl:call-template name="documentGeneral"/>
          </div>
        </div>

      </body>
    </html>



    <!-- BEGIN TEMPLATES -->
  </xsl:template>
  <!-- generate table of contents -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="make-tableofcontents">

    <nav class="cda-render hidden-print hidden-xs hidden-sm affix toc-box" id="navbar-cda">
      <div class="container-fluid cda-render toc-header-container">
        <xsl:if test="$logo-location">
          <div class="col-md-1">
            <img src="logo.png" class="img-responsive" alt="Logo">
              <xsl:attribute name="src">
                <xsl:value-of select="$logo-location"/>
              </xsl:attribute>
            </img>
          </div>
        </xsl:if>
        <div class="cda-render toc-header">
          <xsl:for-each select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole">
            <xsl:call-template name="show-name">
              <xsl:with-param name="name" select="n1:patient/n1:name"/>
            </xsl:call-template>
          </xsl:for-each>
        </div>
        <div class="cda-render toc-header">
          <xsl:value-of select="$title"/>
        </div>
      </div>
      <ul class="cda-render nav nav-stacked fixed" id="navbar-list-cda">
        <li>
          <a class="cda-render lantana-toc" href="#top">BACK TO TOP</a>
        </li>
        <li>
          <a class="cda-render lantana-toc" href="#cda-patient">DEMOGRAPHICS</a>
        </li>
        <li>
          <a class="cda-render lantana-toc" href="#author-performer">AUTHORING DETAILS</a>
        </li>
        <li>
          <a class="cda-render lantana-toc bold" href="#doc-clinical-info">Clinical Sections</a>
          <ul class="cda-render nav nav-stacked fixed" id="navbar-list-cda-sortable">
            <xsl:for-each select="n1:component/n1:structuredBody/n1:component/n1:section/n1:title">
              <li>
                <a class="cda-render lantana-toc" href="#{generate-id(.)}">
                  <xsl:value-of select="."/>
                </a>
              </li>
            </xsl:for-each>
          </ul>
        </li>
        <li>
          <a class="cda-render lantana-toc" href="#doc-info">SIGNATURES</a>
        </li>
      </ul>
    </nav>
  </xsl:template>
  <!-- header elements -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="documentGeneral">
    <div class="container-fluid">
      <h2 class="section-title col-md-6">
        <xsl:text>Document Information</xsl:text>
      </h2>
      <div class="table-responsive col-md-6">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>
                <xsl:text>Document Identifier</xsl:text>
              </th>
              <th>
                <xsl:text>Document Created</xsl:text>
              </th>
            </tr>

          </thead>
          <tbody>
            <tr>
              <td>
                <xsl:call-template name="show-id">
                  <xsl:with-param name="id" select="n1:id"/>
                </xsl:call-template>
              </td>
              <td>
                <xsl:call-template name="show-time">
                  <xsl:with-param name="datetime" select="n1:effectiveTime"/>
                </xsl:call-template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </xsl:template>
  <!-- confidentiality -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="confidentiality">
    <table class="header_table">
      <tbody>
        <td class="td_header_role_name">
          <xsl:text>Confidentiality</xsl:text>
        </td>
        <td class="td_header_role_value">
          <xsl:choose>
            <xsl:when test="n1:confidentialityCode/@code = 'N'">
              <xsl:text>Normal</xsl:text>
            </xsl:when>
            <xsl:when test="n1:confidentialityCode/@code = 'R'">
              <xsl:text>Restricted</xsl:text>
            </xsl:when>
            <xsl:when test="n1:confidentialityCode/@code = 'V'">
              <xsl:text>Very restricted</xsl:text>
            </xsl:when>
          </xsl:choose>
          <xsl:if test="n1:confidentialityCode/n1:originalText">
            <xsl:text> </xsl:text>
            <xsl:value-of select="n1:confidentialityCode/n1:originalText"/>
          </xsl:if>
        </td>
      </tbody>
    </table>
  </xsl:template>
  <!-- author -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="author">
    <xsl:if test="n1:author">
      <div class="header container-fluid">
        <xsl:for-each select="n1:author/n1:assignedAuthor">
          <div class="container-fluid">
            <div class="col-md-6">
              <h2 class="section-title col-md-6" id="author-performer">
                <xsl:text>Author</xsl:text>
              </h2>
              <div class="header-group-content col-md-8">
                <xsl:choose>
                  <xsl:when test="n1:assignedPerson/n1:name">
                    <xsl:call-template name="show-name">
                      <xsl:with-param name="name" select="n1:assignedPerson/n1:name"/>
                    </xsl:call-template>
                    <xsl:if test="n1:representedOrganization">
                      <xsl:text> - </xsl:text>
                      <xsl:call-template name="show-name">
                        <xsl:with-param name="name" select="n1:representedOrganization/n1:name"/>
                      </xsl:call-template>
                    </xsl:if>
                  </xsl:when>
                  <xsl:when test="n1:assignedAuthoringDevice/n1:softwareName">
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="n1:assignedAuthoringDevice/n1:softwareName"/>
                    </xsl:call-template>

                  </xsl:when>
                  <xsl:when test="n1:representedOrganization">
                    <xsl:call-template name="show-name">
                      <xsl:with-param name="name" select="n1:representedOrganization/n1:name"/>
                    </xsl:call-template>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:for-each select="n1:id">
                      <xsl:call-template name="show-id">
                        <xsl:with-param name="id" select="."/>
                      </xsl:call-template>
                    </xsl:for-each>
                  </xsl:otherwise>
                </xsl:choose>
              </div>
            </div>
            <div class="col-md-6">
              <xsl:if test="n1:addr | n1:telecom">
                <h2 class="section-title col-md-6">Contact</h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-contactInfo">
                    <xsl:with-param name="contact" select="."/>
                  </xsl:call-template>
                </div>
              </xsl:if>
            </div>
          </div>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>
  <!--  authenticator -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="authenticator">
    <xsl:if test="n1:authenticator">
      <div class="header container-fluid">
        <xsl:for-each select="n1:authenticator">
          <div class="col-md-6">
            <h2 class="section-title col-md-6">
              <xsl:text>Signed</xsl:text>
            </h2>
            <div class="header-group-content col-md-8">
              <xsl:call-template name="show-name">
                <xsl:with-param name="name" select="n1:assignedEntity/n1:assignedPerson/n1:name"/>
              </xsl:call-template>
              <xsl:text> at </xsl:text>
              <xsl:call-template name="show-time">
                <xsl:with-param name="datetime" select="n1:time"/>
              </xsl:call-template>
            </div>
          </div>
          <div class="col-md-6">
            <xsl:if test="n1:assignedEntity/n1:addr | n1:assignedEntity/n1:telecom">
              <h2 class="section-title col-md-6">
                <xsl:text>Contact</xsl:text>
              </h2>
              <div class="header-group-content col-md-8">
                <xsl:call-template name="show-contactInfo">
                  <xsl:with-param name="contact" select="n1:assignedEntity"/>
                </xsl:call-template>
              </div>
            </xsl:if>
          </div>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- legalAuthenticator -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="legalAuthenticator">
    <div class="container-fluid">
      <xsl:if test="n1:legalAuthenticator">
        <div class="header container-fluid">
          <div class="col-md-6">
            <h2 class="section-title col-md-6">
              <xsl:text>Legal authenticator</xsl:text>
            </h2>
            <div class="header-group-content col-md-8">
              <xsl:call-template name="show-assignedEntity">
                <xsl:with-param name="asgnEntity" select="n1:legalAuthenticator/n1:assignedEntity"/>
              </xsl:call-template>
              <xsl:text> </xsl:text>
              <xsl:call-template name="show-sig">
                <xsl:with-param name="sig" select="n1:legalAuthenticator/n1:signatureCode"/>
              </xsl:call-template>
              <xsl:if test="n1:legalAuthenticator/n1:time/@value">
                <xsl:text> at </xsl:text>
                <xsl:call-template name="show-time">
                  <xsl:with-param name="datetime" select="n1:legalAuthenticator/n1:time"/>
                </xsl:call-template>
              </xsl:if>
            </div>
          </div>
          <xsl:if test="n1:legalAuthenticator/n1:assignedEntity/n1:addr | n1:legalAuthenticator/n1:assignedEntity/n1:telecom">
            <div class="col-md-6">
              <h2 class="col-md-6 section-title">Contact</h2>
              <div class="header-group-content col-md-8">
                <xsl:call-template name="show-contactInfo">
                  <xsl:with-param name="contact" select="n1:legalAuthenticator/n1:assignedEntity"/>
                </xsl:call-template>
              </div>
            </div>
          </xsl:if>
        </div>
      </xsl:if>
    </div>
  </xsl:template>
  <!-- dataEnterer -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="dataEnterer">
    <xsl:if test="n1:dataEnterer">
      <div class="container-fluid header">
        <div class="col-md-6">
          <h2 class="section-title col-md-6">
            <xsl:text>Entered by</xsl:text>
          </h2>
          <div class="col-md-6 header-group-content">
            <xsl:call-template name="show-assignedEntity">
              <xsl:with-param name="asgnEntity" select="n1:dataEnterer/n1:assignedEntity"/>
            </xsl:call-template>
          </div>
        </div>
        <div class="col-md-6">
          <xsl:if test="n1:dataEnterer/n1:assignedEntity/n1:addr | n1:dataEnterer/n1:assignedEntity/n1:telecom">
            <h2 class="section-title col-md-6">
              <xsl:text>Contact</xsl:text>
            </h2>
            <div class="col-md-6 header-group-content">
              <xsl:call-template name="show-contactInfo">
                <xsl:with-param name="contact" select="n1:dataEnterer/n1:assignedEntity"/>
              </xsl:call-template>
            </div>
          </xsl:if>
        </div>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- componentOf -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="componentOf">
    <xsl:if test="n1:componentOf">
      <div class="header container-fluid">
        <xsl:for-each select="n1:componentOf/n1:encompassingEncounter">
          <div class="container-fluid col-md-8">
            <div class="container-fluid">
              <h2 class="section-title col-md-10">
                <xsl:text>Encounter</xsl:text>
              </h2>
              <div class="header-group-content col-md-10">
                <xsl:if test="n1:id">
                  <xsl:choose>
                    <xsl:when test="n1:code">
                      <div class="row">
                        <div class="attribute-title col-md-2">
                          <xsl:text>Identifier</xsl:text>
                        </div>
                        <div class="col-md-6">
                          <xsl:call-template name="show-id">
                            <xsl:with-param name="id" select="n1:id"/>
                          </xsl:call-template>
                        </div>
                      </div>
                      <div class="row">
                        <div class="attribute-title col-md-2">
                          <xsl:text>Type</xsl:text>
                        </div>
                        <div class="col-md-6">
                          <xsl:call-template name="show-code">
                            <xsl:with-param name="code" select="n1:code"/>
                          </xsl:call-template>
                        </div>
                      </div>
                    </xsl:when>
                    <xsl:otherwise>
                      <div class="row">
                        <div class="attribute-title col-md-2">
                          <xsl:text>Identifier</xsl:text>
                        </div>
                        <div class="col-md-6">
                          <xsl:call-template name="show-id">
                            <xsl:with-param name="id" select="n1:id"/>
                          </xsl:call-template>
                        </div>
                      </div>
                    </xsl:otherwise>
                  </xsl:choose>
                </xsl:if>
                <div class="row">
                  <div class="attribute-title col-md-2">
                    <xsl:text>Date</xsl:text>
                  </div>
                  <xsl:if test="n1:effectiveTime">
                    <xsl:choose>
                      <xsl:when test="n1:effectiveTime/@value">
                        <div class="col-md-4">
                          <xsl:call-template name="show-time">
                            <xsl:with-param name="datetime" select="n1:effectiveTime"/>
                          </xsl:call-template>
                        </div>
                      </xsl:when>
                      <xsl:when test="n1:effectiveTime/n1:low">
                        <div class="col-md-4">
                          <span class="attribute-title">
                            <xsl:text>From: </xsl:text>
                          </span>
                          <xsl:call-template name="show-time">
                            <xsl:with-param name="datetime" select="n1:effectiveTime/n1:low"/>
                          </xsl:call-template>
                        </div>
                        <xsl:if test="n1:effectiveTime/n1:high">
                          <div class="col-md-4">
                            <span class="attribute-title">
                              <xsl:text>To: </xsl:text>
                            </span>
                            <xsl:call-template name="show-time">
                              <xsl:with-param name="datetime" select="n1:effectiveTime/n1:high"/>
                            </xsl:call-template>
                          </div>
                        </xsl:if>
                      </xsl:when>
                    </xsl:choose>
                  </xsl:if>
                </div>
                <xsl:if test="n1:location/n1:healthCareFacility">
                  <div class="row">
                    <div class="attribute-title col-md-2">
                      <xsl:text>Location</xsl:text>
                    </div>
                    <div class="col-md-6">
                      <xsl:choose>
                        <xsl:when test="n1:location/n1:healthCareFacility/n1:location/n1:name">
                          <xsl:call-template name="show-name">
                            <xsl:with-param name="name" select="n1:location/n1:healthCareFacility/n1:location/n1:name"/>
                          </xsl:call-template>
                          <xsl:for-each select="n1:location/n1:healthCareFacility/n1:serviceProviderOrganization/n1:name">
                            <xsl:text> of </xsl:text>
                            <xsl:call-template name="show-name">
                              <xsl:with-param name="name" select="n1:location/n1:healthCareFacility/n1:serviceProviderOrganization/n1:name"/>
                            </xsl:call-template>
                          </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="n1:location/n1:healthCareFacility/n1:code">
                          <xsl:call-template name="show-code">
                            <xsl:with-param name="code" select="n1:location/n1:healthCareFacility/n1:code"/>
                          </xsl:call-template>
                        </xsl:when>
                        <xsl:otherwise>
                          <xsl:if test="n1:location/n1:healthCareFacility/n1:id">
                            <span class="attribute-title">
                              <xsl:text>ID: </xsl:text>
                            </span>
                            <xsl:for-each select="n1:location/n1:healthCareFacility/n1:id">
                              <xsl:call-template name="show-id">
                                <xsl:with-param name="id" select="."/>
                              </xsl:call-template>
                            </xsl:for-each>
                          </xsl:if>
                        </xsl:otherwise>
                      </xsl:choose>
                    </div>
                  </div>
                </xsl:if>
              </div>
              <xsl:if test="n1:responsibleParty">
                <div class="col-md-6">
                  <h2 class="section-title col-md-6">
                    <xsl:text>Responsible Party</xsl:text>
                  </h2>
                  <div class="header-group-content col-md-8">
                    <xsl:call-template name="show-assignedEntity">
                      <xsl:with-param name="asgnEntity" select="n1:responsibleParty/n1:assignedEntity"/>
                    </xsl:call-template>
                  </div>
                </div>
              </xsl:if>
              <xsl:if test="n1:responsibleParty/n1:assignedEntity/n1:addr | n1:responsibleParty/n1:assignedEntity/n1:telecom">
                <div class="col-md-6">
                  <h2 class="section-title col-md-6">
                    <xsl:text>Contact</xsl:text>
                  </h2>
                  <div class="header-group-content col-md-8">
                    <xsl:call-template name="show-contactInfo">
                      <xsl:with-param name="contact" select="n1:responsibleParty/n1:assignedEntity"/>
                    </xsl:call-template>
                  </div>
                </div>
              </xsl:if>
            </div>
          </div>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- custodian -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="custodian">
    <xsl:if test="n1:custodian">
      <div class="container-fluid header">
        <div class="col-md-6">
          <h2 class="section-title col-md-6">
            <xsl:text>Document maintained by</xsl:text>
          </h2>
          <div class="header-group-content col-md-8">
            <xsl:choose>
              <xsl:when test="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:name">
                <xsl:call-template name="show-name">
                  <xsl:with-param name="name" select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:name"/>
                </xsl:call-template>
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:id">
                  <xsl:call-template name="show-id"/>
                  <xsl:if test="position() != last()"> </xsl:if>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
          </div>
        </div>
        <xsl:if test="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:addr | n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:telecom">
          <div class="col-md-6">
            <h2 class="section-title col-md-6"> Contact </h2>
            <div class="header-group-content col-md-8">
              <xsl:call-template name="show-contactInfo">
                <xsl:with-param name="contact" select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization"/>
              </xsl:call-template>
            </div>
          </div>
        </xsl:if>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- documentationOf -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="documentationOf">
    <xsl:if test="n1:documentationOf">
      <div class="header container-fluid">
        <xsl:for-each select="n1:documentationOf">
          <xsl:if test="n1:serviceEvent/@classCode and n1:serviceEvent/n1:code">
            <div class="container-fluid">
              <div class="container-fluid">
                <xsl:variable name="displayName">
                  <xsl:call-template name="show-actClassCode">
                    <xsl:with-param name="clsCode" select="n1:serviceEvent/@classCode"/>
                  </xsl:call-template>
                </xsl:variable>
                <xsl:if test="$displayName">
                  <div class="col-md-6">
                    <h2 class="section-title">
                      <xsl:call-template name="firstCharCaseUp">
                        <xsl:with-param name="data" select="$displayName"/>
                      </xsl:call-template>
                    </h2>
                  </div>
                  <div class="header-group-content col-md-8">
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="n1:serviceEvent/n1:code"/>
                    </xsl:call-template>
                    <xsl:if test="n1:serviceEvent/n1:effectiveTime">
                      <xsl:choose>
                        <xsl:when test="n1:serviceEvent/n1:effectiveTime/@value">
                          <xsl:text> at </xsl:text>
                          <xsl:call-template name="show-time">
                            <xsl:with-param name="datetime" select="n1:serviceEvent/n1:effectiveTime"/>
                          </xsl:call-template>
                        </xsl:when>
                        <xsl:when test="n1:serviceEvent/n1:effectiveTime/n1:low">
                          <xsl:text> from </xsl:text>
                          <xsl:call-template name="show-time">
                            <xsl:with-param name="datetime" select="n1:serviceEvent/n1:effectiveTime/n1:low"/>
                          </xsl:call-template>
                          <xsl:if test="n1:serviceEvent/n1:effectiveTime/n1:high">
                            <xsl:text> to </xsl:text>
                            <xsl:call-template name="show-time">
                              <xsl:with-param name="datetime" select="n1:serviceEvent/n1:effectiveTime/n1:high"/>
                            </xsl:call-template>
                          </xsl:if>
                        </xsl:when>
                      </xsl:choose>
                    </xsl:if>
                  </div>
                </xsl:if>
              </div>
            </div>
          </xsl:if>
          <xsl:for-each select="n1:serviceEvent/n1:performer">
            <div class="header-group container-fluid">
              <xsl:variable name="displayName">
                <xsl:call-template name="show-participationType">
                  <xsl:with-param name="ptype" select="@typeCode"/>
                </xsl:call-template>
                <xsl:if test="n1:functionCode/@code">
                  <xsl:text> </xsl:text>
                  <xsl:call-template name="show-participationFunction">
                    <xsl:with-param name="pFunction" select="n1:functionCode/@code"/>
                  </xsl:call-template>
                </xsl:if>
              </xsl:variable>
              <div class="container-fluid">
                <h2 class="section-title col-md-6" id="service-event">
                  <xsl:text>Service Event</xsl:text>
                </h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-assignedEntity">
                    <xsl:with-param name="asgnEntity" select="n1:assignedEntity"/>
                  </xsl:call-template>
                </div>
                <div class="header-group-content col-md-8">
                  <xsl:if test="../n1:effectiveTime/n1:low">
                    <xsl:call-template name="show-time">
                      <xsl:with-param name="datetime" select="../n1:effectiveTime/n1:low"/>
                    </xsl:call-template>
                  </xsl:if>

                  <xsl:if test="../n1:effectiveTime/n1:high"> - <xsl:call-template name="show-time">
                      <xsl:with-param name="datetime" select="../n1:effectiveTime/n1:high"/>
                    </xsl:call-template>
                  </xsl:if>
                </div>
              </div>
            </div>
          </xsl:for-each>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- inFulfillmentOf -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="inFulfillmentOf">
    <xsl:if test="n1:infulfillmentOf">
      <xsl:for-each select="n1:inFulfillmentOf">
        <xsl:text>In fulfillment of</xsl:text>
        <xsl:for-each select="n1:order">
          <xsl:for-each select="n1:id">
            <xsl:call-template name="show-id"/>
          </xsl:for-each>
          <xsl:for-each select="n1:code">
            <xsl:text> </xsl:text>
            <xsl:call-template name="show-code">
              <xsl:with-param name="code" select="."/>
            </xsl:call-template>
          </xsl:for-each>
          <xsl:for-each select="n1:priorityCode">
            <xsl:text> </xsl:text>
            <xsl:call-template name="show-code">
              <xsl:with-param name="code" select="."/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:for-each>
      </xsl:for-each>
    </xsl:if>
  </xsl:template>
  <!-- informant -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="informant">
    <xsl:if test="n1:informant">
      <div class="header container-fluid">
        <xsl:for-each select="n1:informant">
          <div class="container-fluid">
            <div class="col-md-6">
              <h2 class="section-title col-md-6">
                <xsl:text>Informant</xsl:text>
              </h2>
              <div class="header-group-content col-md-8">
                <xsl:if test="n1:assignedEntity">
                  <xsl:call-template name="show-assignedEntity">
                    <xsl:with-param name="asgnEntity" select="n1:assignedEntity"/>
                  </xsl:call-template>
                </xsl:if>
                <xsl:if test="n1:relatedEntity">
                  <xsl:call-template name="show-relatedEntity">
                    <xsl:with-param name="relatedEntity" select="n1:relatedEntity"/>
                  </xsl:call-template>
                </xsl:if>
              </div>
            </div>
            <xsl:choose>
              <xsl:when test="n1:assignedEntity/n1:addr | n1:assignedEntity/n1:telecom">
                <div class="col-md-6">
                  <h2 class="section-title col-md-6">
                    <xsl:text>Contact</xsl:text>
                  </h2>
                  <div class="header-group-content col-md-8">
                    <xsl:if test="n1:assignedEntity">
                      <xsl:call-template name="show-contactInfo">
                        <xsl:with-param name="contact" select="n1:assignedEntity"/>
                      </xsl:call-template>
                    </xsl:if>
                  </div>
                </div>
              </xsl:when>
              <xsl:when test="n1:relatedEntity/n1:addr | n1:relatedEntity/n1:telecom">
                <div class="col-md-6">
                  <h2 class="col-md-6 section-title">
                    <xsl:text>Contact</xsl:text>
                  </h2>
                  <div class="col-md-6 header-group-content">
                    <xsl:if test="n1:relatedEntity">
                      <xsl:call-template name="show-contactInfo">
                        <xsl:with-param name="contact" select="n1:relatedEntity"/>
                      </xsl:call-template>
                    </xsl:if>
                  </div>
                </div>
              </xsl:when>
            </xsl:choose>
          </div>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- informantionRecipient -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="informationRecipient">
    <div class="container-fluid">
      <xsl:if test="n1:informationRecipient">
        <div class="container-fluid header">
          <xsl:for-each select="n1:informationRecipient">
            <div class="container-fluid">
              <h2 class="section-title col-md-6">
                <xsl:text>Information Recipient</xsl:text>
              </h2>
              <div class="col-md-6 header-group-content">
                <xsl:choose>
                  <xsl:when test="n1:intendedRecipient/n1:informationRecipient/n1:name">
                    <xsl:for-each select="n1:intendedRecipient/n1:informationRecipient">
                      <xsl:call-template name="show-name">
                        <xsl:with-param name="name" select="n1:name"/>
                      </xsl:call-template>
                      <xsl:if test="position() != last()"> </xsl:if>
                    </xsl:for-each>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:for-each select="n1:intendedRecipient">
                      <xsl:for-each select="n1:id">
                        <xsl:call-template name="show-id"/>
                      </xsl:for-each>
                      <xsl:if test="position() != last()"> </xsl:if>
                    </xsl:for-each>
                  </xsl:otherwise>
                </xsl:choose>
              </div>
              <div class="col-md-6">
                <xsl:if test="n1:intendedRecipient/n1:addr | n1:intendedRecipient/n1:telecom">
                  <h2 class="section-title col-md-6">
                    <xsl:text>Contact</xsl:text>
                  </h2>
                  <div class="col-md-6">
                    <xsl:call-template name="show-contactInfo">
                      <xsl:with-param name="contact" select="n1:intendedRecipient"/>
                    </xsl:call-template>
                  </div>
                </xsl:if>
              </div>
            </div>
          </xsl:for-each>
        </div>
      </xsl:if>
    </div>
  </xsl:template>
  <!-- participant -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="participant">
    <div class="container-fluid">
      <xsl:if test="n1:participant">
        <div class="header container-fluid">
          <xsl:for-each select="n1:participant">
            <xsl:if test="not(n1:associatedEntity/@classCode = 'ECON' or n1:associatedEntity/@classCode = 'NOK')">
              <xsl:variable name="participtRole">
                <xsl:call-template name="translateRoleAssoCode">
                  <xsl:with-param name="classCode" select="n1:associatedEntity/@classCode"/>
                  <xsl:with-param name="code" select="n1:associatedEntity/n1:code"/>
                </xsl:call-template>
              </xsl:variable>
              <div class="col-md-6">
                <h2 class="col-md-6 section-title">
                  <xsl:choose>
                    <xsl:when test="$participtRole">
                      <xsl:call-template name="firstCharCaseUp">
                        <xsl:with-param name="data" select="$participtRole"/>
                      </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:text>Participant</xsl:text>
                    </xsl:otherwise>
                  </xsl:choose>
                </h2>
                <div class="header-group-content col-md-8">
                  <xsl:if test="n1:functionCode">
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="n1:functionCode"/>
                    </xsl:call-template>
                  </xsl:if>
                  <xsl:call-template name="show-associatedEntity">
                    <xsl:with-param name="assoEntity" select="n1:associatedEntity"/>
                  </xsl:call-template>
                  <xsl:if test="n1:time">
                    <xsl:if test="n1:time/n1:low">
                      <xsl:text> from </xsl:text>
                      <xsl:call-template name="show-time">
                        <xsl:with-param name="datetime" select="n1:time/n1:low"/>
                      </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="n1:time/n1:high">
                      <xsl:text> to </xsl:text>
                      <xsl:call-template name="show-time">
                        <xsl:with-param name="datetime" select="n1:time/n1:high"/>
                      </xsl:call-template>
                    </xsl:if>
                  </xsl:if>
                  <xsl:if test="position() != last()">
                    <br/>
                  </xsl:if>
                </div>
              </div>
              <div class="col-md-6">
                <xsl:if test="n1:associatedEntity/n1:addr | n1:associatedEntity/n1:telecom">
                  <h2 class="section-title col-md-6">
                    <xsl:text>Contact</xsl:text>
                  </h2>
                  <div class="col-md-6 header-group-content">
                    <xsl:call-template name="show-contactInfo">
                      <xsl:with-param name="contact" select="n1:associatedEntity"/>
                    </xsl:call-template>
                  </div>
                </xsl:if>
              </div>
            </xsl:if>
          </xsl:for-each>
        </div>
      </xsl:if>
    </div>
  </xsl:template>

  <!-- recordTarget / Patient -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="recordTarget">
    <div class="header container-fluid" id="cda-patient">
      <xsl:for-each select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole">
        <xsl:if test="not(n1:id/@nullFlavor)">
          <div class="patient-heading container-fluid">
            <div class="patient-name row">
              <xsl:call-template name="show-name">
                <xsl:with-param name="name" select="n1:patient/n1:name"/>
              </xsl:call-template>
            </div>
            <div class="patient-identifier container-fluid">
              <div class="attribute-title row">Patient Identifiers</div>
              <xsl:for-each select="n1:id">
                <div class="row">
                  <div class="col-md-6 patient-id">
                    <xsl:call-template name="show-id"/>
                  </div>
                </div>
              </xsl:for-each>
            </div>
          </div>
          <div class="patient-info container-fluid">
            <div class="col-md-6">
              <h2 class="section-title col-md-6">About</h2>
              <div class="header-group-content col-md-8">
                <div class="row">
                  <div class="attribute-title col-md-6">
                    <xsl:text>Date of Birth</xsl:text>
                  </div>
                  <div class="col-md-6">
                    <xsl:call-template name="show-time">
                      <xsl:with-param name="datetime" select="n1:patient/n1:birthTime"/>
                    </xsl:call-template>
                  </div>
                </div>
                <div class="row">
                  <div class="attribute-title col-md-6">
                    <xsl:text>Sex</xsl:text>
                  </div>
                  <div class="col-md-6">
                    <xsl:for-each select="n1:patient/n1:administrativeGenderCode">
                      <xsl:call-template name="show-gender"/>
                    </xsl:for-each>
                  </div>
                </div>
                <xsl:if test="n1:patient/n1:raceCode | (n1:patient/n1:ethnicGroupCode)">
                  <div class="row">
                    <div class="attribute-title col-md-6">
                      <xsl:text>Race</xsl:text>
                    </div>
                    <div class="col-md-6">
                      <xsl:choose>
                        <xsl:when test="n1:patient/n1:raceCode">
                          <xsl:for-each select="n1:patient/n1:raceCode">
                            <xsl:call-template name="show-race-ethnicity"/>
                          </xsl:for-each>
                        </xsl:when>
                        <xsl:otherwise>
                          <span class="generated-text">
                            <xsl:text>Information not available</xsl:text>
                          </span>
                        </xsl:otherwise>
                      </xsl:choose>
                    </div>
                  </div>
                  <div class="row">
                    <div class="attribute-title col-md-6">
                      <xsl:text>Ethnicity</xsl:text>
                    </div>
                    <div class="col-md-6">
                      <xsl:choose>
                        <xsl:when test="n1:patient/n1:ethnicGroupCode">
                          <xsl:for-each select="n1:patient/n1:ethnicGroupCode">
                            <xsl:call-template name="show-race-ethnicity"/>
                          </xsl:for-each>
                        </xsl:when>
                        <xsl:otherwise>
                          <span class="generated-text">
                            <xsl:text>Information not available</xsl:text>
                          </span>
                        </xsl:otherwise>
                      </xsl:choose>
                    </div>
                  </div>
                </xsl:if>
              </div>
            </div>
            <div class="col-md-6">
              <h2 class="section-title col-md-6">
                <xsl:text>Contact</xsl:text>
              </h2>
              <div class="header-group-content col-md-8">
                <xsl:call-template name="show-contactInfo">
                  <xsl:with-param name="contact" select="."/>
                </xsl:call-template>
              </div>
            </div>
          </div>
        </xsl:if>
      </xsl:for-each>
      <!-- list all the emergency contacts -->
      <xsl:if test="n1:participant">
        <xsl:for-each select="n1:participant">
          <xsl:if test="n1:associatedEntity/@classCode = 'ECON'">
            <div class="container-fluid" id="emergency-contact">
              <div class="col-md-6">
                <h2 class="section-title col-md-6">Emergency Contact</h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-associatedEntity">
                    <xsl:with-param name="assoEntity" select="n1:associatedEntity"/>
                  </xsl:call-template>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="section-title col-md-6">Contact</h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-contactInfo">
                    <xsl:with-param name="contact" select="n1:associatedEntity"/>
                  </xsl:call-template>
                </div>
              </div>
            </div>
          </xsl:if>
        </xsl:for-each>
      </xsl:if>

      <!-- list nex of kin-->
      <xsl:if test="n1:participant">
        <xsl:for-each select="n1:participant">
          <xsl:if test="n1:associatedEntity/@classCode = 'NOK'">
            <div class="container-fluid" id="emergency-contact">
              <div class="col-md-6">
                <h2 class="section-title col-md-6">Next of Kin</h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-associatedEntity">
                    <xsl:with-param name="assoEntity" select="n1:associatedEntity"/>
                  </xsl:call-template>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="section-title col-md-6">Contact</h2>
                <div class="header-group-content col-md-8">
                  <xsl:call-template name="show-contactInfo">
                    <xsl:with-param name="contact" select="n1:associatedEntity"/>
                  </xsl:call-template>
                </div>
              </div>
            </div>
          </xsl:if>
        </xsl:for-each>
      </xsl:if>
    </div>

  </xsl:template>
  <!-- relatedDocument -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="relatedDocument">
    <xsl:if test="n1:relatedDocument">
      <table class="header_table">
        <tbody>
          <xsl:for-each select="n1:relatedDocument">
            <tr>
              <td class="td_header_role_name">
                <span class="td_label">
                  <xsl:text>Related document</xsl:text>
                </span>
              </td>
              <td class="td_header_role_value">
                <xsl:for-each select="n1:parentDocument">
                  <xsl:for-each select="n1:id">
                    <xsl:call-template name="show-id"/>
                    <br/>
                  </xsl:for-each>
                </xsl:for-each>
              </td>
            </tr>
          </xsl:for-each>
        </tbody>
      </table>
    </xsl:if>
  </xsl:template>
  <!-- authorization (consent) -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="authorization">
    <xsl:if test="n1:authorization">
      <table class="header_table">
        <tbody>
          <xsl:for-each select="n1:authorization">
            <tr>
              <td class="td_header_role_name">
                <span class="td_label">
                  <xsl:text>Consent</xsl:text>
                </span>
              </td>
              <td class="td_header_role_value">
                <xsl:choose>
                  <xsl:when test="n1:consent/n1:code">
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="n1:consent/n1:code"/>
                    </xsl:call-template>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="n1:consent/n1:statusCode"/>
                    </xsl:call-template>
                  </xsl:otherwise>
                </xsl:choose>
                <br/>
              </td>
            </tr>
          </xsl:for-each>
        </tbody>
      </table>
    </xsl:if>
  </xsl:template>
  <!-- setAndVersion -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="setAndVersion">
    <xsl:if test="n1:setId and n1:versionNumber">
      <table class="header_table">
        <tbody>
          <tr>
            <td class="td_header_role_name">
              <xsl:text>SetId and Version</xsl:text>
            </td>
            <td class="td_header_role_value">
              <xsl:text>SetId: </xsl:text>
              <xsl:call-template name="show-id">
                <xsl:with-param name="id" select="n1:setId"/>
              </xsl:call-template>
              <xsl:text>  Version: </xsl:text>
              <xsl:value-of select="n1:versionNumber/@value"/>
            </td>
          </tr>
        </tbody>
      </table>
    </xsl:if>
  </xsl:template>
  <!-- show StructuredBody  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:component/n1:structuredBody">
    <xsl:for-each select="n1:component/n1:section">
      <xsl:call-template name="section"/>
    </xsl:for-each>
  </xsl:template>
  <!-- show nonXMLBody -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:component/n1:nonXMLBody">
    <xsl:choose>
      <!-- if there is a reference, use that in an IFRAME -->
      <xsl:when test="n1:text/n1:reference">
        <xsl:variable name="source" select="string(n1:text/n1:reference/@value)"/>
        <xsl:variable name="mediaType" select="string(n1:text/@mediaType)"/>
        <xsl:variable name="lcSource" select="translate($source, $uc, $lc)"/>
        <xsl:variable name="scrubbedSource" select="translate($source, $simple-sanitizer-match, $simple-sanitizer-replace)"/>
        <xsl:message>
<xsl:value-of select="$source"/>, <xsl:value-of select="$lcSource"/>
</xsl:message>
        <xsl:choose>
          <xsl:when test="contains($lcSource, 'javascript')">
            <p>
              <xsl:value-of select="$javascript-injection-warning"/>
            </p>
            <xsl:message>
              <xsl:value-of select="$javascript-injection-warning"/>
            </xsl:message>
          </xsl:when>
          <xsl:when test="not($source = $scrubbedSource)">
            <p>
              <xsl:value-of select="$malicious-content-warning"/>
            </p>
            <xsl:message>
              <xsl:value-of select="$malicious-content-warning"/>
            </xsl:message>
          </xsl:when>
          <xsl:otherwise>
            <iframe name="nonXMLBody" id="nonXMLBody" WIDTH="80%" HEIGHT="600" src="{$source}">
              <html>
                <body>
                  <object data="{$source}" type="{$mediaType}">
                    <embed src="{$source}" type="{$mediaType}"/>
                  </object>
                </body>
              </html>
            </iframe>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="n1:text/@mediaType = &quot;text/plain&quot;">
        <pre>
<xsl:value-of select="n1:text/text()"/>
</pre>
      </xsl:when>
      <xsl:otherwise>
        <pre>Cannot display the text</pre>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- top level component/section: display title and text,
      and process any nested component/sections
    -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="section">
    <div class="container-fluid header">
      <xsl:call-template name="section-title">
        <xsl:with-param name="title" select="n1:title"/>
      </xsl:call-template>
      <xsl:call-template name="section-author"/>
      <xsl:call-template name="section-text"/>
      <xsl:for-each select="n1:component/n1:section">
        <div class="container-fluid">
          <xsl:call-template name="nestedSection">
            <xsl:with-param name="margin" select="2"/>
          </xsl:call-template>
        </div>
      </xsl:for-each>
    </div>
  </xsl:template>
  <!-- top level section title -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="section-title">
    <xsl:param name="title"/>
    <h1 class="section-title" id="{generate-id($title)}" ng-click="gotoAnchor('toc')">
      <xsl:value-of select="$title"/>
    </h1>
  </xsl:template>

  <!-- section author -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="section-author">
    <xsl:if test="count(n1:author) &gt; 0">
      <div class="section-author">
        <span class="emphasis">
          <xsl:text>Section Author: </xsl:text>
        </span>
        <xsl:for-each select="n1:author/n1:assignedAuthor">
          <xsl:choose>
            <xsl:when test="n1:assignedPerson/n1:name">
              <xsl:call-template name="show-name">
                <xsl:with-param name="name" select="n1:assignedPerson/n1:name"/>
              </xsl:call-template>
              <xsl:if test="n1:representedOrganization">
                <xsl:text>, </xsl:text>
                <xsl:call-template name="show-name">
                  <xsl:with-param name="name" select="n1:representedOrganization/n1:name"/>
                </xsl:call-template>
              </xsl:if>
            </xsl:when>
            <xsl:when test="n1:assignedAuthoringDevice/n1:softwareName">
              <xsl:call-template name="show-code">
                <xsl:with-param name="code" select="n1:assignedAuthoringDevice/n1:softwareName"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:for-each select="n1:id">
                <xsl:call-template name="show-id"/>
                <br/>
              </xsl:for-each>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
        <br/>
      </div>
    </xsl:if>
  </xsl:template>
  <!-- top-level section Text   -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="section-text">
    <div class="section-text">
      <xsl:apply-templates select="n1:text"/>
    </div>
  </xsl:template>
  <!-- nested component/section -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="nestedSection">
    <xsl:param name="margin"/>
    <h4>
      <xsl:value-of select="n1:title"/>
    </h4>
    <div class="nested-section" style="margin-left : {$margin}em;">
      <xsl:apply-templates select="n1:text"/>
    </div>
    <xsl:for-each select="n1:component/n1:section">
      <xsl:call-template name="nestedSection">
        <xsl:with-param name="margin" select="2 * $margin"/>
      </xsl:call-template>
    </xsl:for-each>
  </xsl:template>
  <!--   paragraph  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:paragraph">
    <xsl:element name="p">
      <xsl:call-template name="output-attrs"/>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <!--   pre format  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:pre">
    <xsl:element name="pre">
      <xsl:call-template name="output-attrs"/>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <!--   Content w/ deleted text is hidden -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:content[@revised = 'delete']"/>
  <!--   content  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:content">
    <xsl:element name="content">
      <xsl:call-template name="output-attrs"/>
      <!--<xsl:apply-templates select="@styleCode"/>-->
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <!-- line break -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:br">
    <xsl:element name="br">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <!--   list  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:list">
    <xsl:if test="n1:caption">
      <p>
        <b>
          <xsl:apply-templates select="n1:caption"/>
        </b>
      </p>
    </xsl:if>
    <ul>
      <xsl:for-each select="n1:item">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:template>
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:list[@styleCode='none']">
    <xsl:if test="n1:caption">
      <p>
        <b>
          <xsl:apply-templates select="n1:caption"/>
        </b>
      </p>
    </xsl:if>
    <ul style="list-style-type:none">
      <xsl:for-each select="n1:item">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:template>
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:list[@listType = 'ordered']">
    <xsl:if test="n1:caption">
      <span style="font-weight:bold; ">
        <xsl:apply-templates select="n1:caption"/>
      </span>
    </xsl:if>
    <ol>
      <xsl:for-each select="n1:item">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </ol>
  </xsl:template>

  <!--   caption  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:caption">
    <xsl:apply-templates/>
    <xsl:text>: </xsl:text>
  </xsl:template>
  <!--  Tables   -->

  <xsl:variable xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="table-elem-attrs">
    <in:tableElems>
      <in:elem name="table">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="summary"/>
        <in:attr name="width"/>
        <!-- Commented out to keep table rendering consistent -->
        <!--<in:attr name="border"/>-->
        <in:attr name="frame"/>
        <in:attr name="rules"/>
        <in:attr name="cellspacing"/>
        <in:attr name="cellpadding"/>
      </in:elem>
      <in:elem name="thead">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="tfoot">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="tbody">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="colgroup">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="span"/>
        <in:attr name="width"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="col">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="span"/>
        <in:attr name="width"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="tr">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="th">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="abbr"/>
        <in:attr name="axis"/>
        <in:attr name="headers"/>
        <in:attr name="scope"/>
        <in:attr name="rowspan"/>
        <in:attr name="colspan"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
      <in:elem name="td">
        <in:attr name="ID"/>
        <in:attr name="language"/>
        <in:attr name="styleCode"/>
        <in:attr name="abbr"/>
        <in:attr name="axis"/>
        <in:attr name="headers"/>
        <in:attr name="scope"/>
        <in:attr name="rowspan"/>
        <in:attr name="colspan"/>
        <in:attr name="align"/>
        <in:attr name="char"/>
        <in:attr name="charoff"/>
        <in:attr name="valign"/>
      </in:elem>
    </in:tableElems>
  </xsl:variable>

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="output-attrs">
    <xsl:variable name="elem-name" select="local-name(.)"/>
    <!-- This assigns all outputted elements the cda-render class -->
    <!-- <xsl:attribute name="class">cda-render</xsl:attribute>-->
    <xsl:choose>
      <xsl:when test="$elem-name = 'table'">
        <xsl:attribute name="class">table table-striped table-hover</xsl:attribute>
      </xsl:when>
    </xsl:choose>
    <xsl:for-each select="@*">
      <xsl:variable name="attr-name" select="local-name(.)"/>
      <xsl:variable name="source" select="."/>
      <xsl:variable name="lcSource" select="translate($source, $uc, $lc)"/>
      <xsl:variable name="scrubbedSource" select="translate($source, $simple-sanitizer-match, $simple-sanitizer-replace)"/>
      <xsl:choose>
        <xsl:when test="contains($lcSource, 'javascript')">
          <p>
            <xsl:value-of select="$javascript-injection-warning"/>
          </p>
          <xsl:message terminate="yes">
            <xsl:value-of select="$javascript-injection-warning"/>
          </xsl:message>
        </xsl:when>
        <xsl:when test="$attr-name = 'styleCode'">
          <xsl:apply-templates select="."/>
        </xsl:when>
        <!--<xsl:when
          test="not(document('')/xsl:stylesheet/xsl:variable[@name = 'table-elem-attrs']/in:tableElems/in:elem[@name = $elem-name]/in:attr[@name = $attr-name])">
          <xsl:message><xsl:value-of select="$attr-name"/> is not legal in <xsl:value-of
              select="$elem-name"/></xsl:message>
        </xsl:when>-->
        <xsl:when test="not($source = $scrubbedSource)">
          <p>
            <xsl:value-of select="$malicious-content-warning"/>
          </p>
          <xsl:message>
            <xsl:value-of select="$malicious-content-warning"/>
          </xsl:message>
        </xsl:when>
        <xsl:otherwise>
          <xsl:copy-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:table">
    <div class="table-responsive">
      <xsl:element name="{local-name()}">
        <xsl:call-template name="output-attrs"/>
        <xsl:apply-templates/>
      </xsl:element>
    </div>
  </xsl:template>

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:thead | n1:tfoot | n1:tbody | n1:colgroup | n1:col | n1:tr | n1:th | n1:td">
    <xsl:element name="{local-name()}">
      <xsl:call-template name="output-attrs"/>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:table/n1:caption">
    <span style="font-weight:bold; ">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:linkHtml">
    <xsl:element name="a">
      <xsl:copy-of select="@* | text()"/>
    </xsl:element>
  </xsl:template>

  <!--   RenderMultiMedia
     this currently only handles GIF's and JPEG's.  It could, however,
     be extended by including other image MIME types in the predicate
     and/or by generating <object> or <applet> tag with the correct
     params depending on the media type  @ID  =$imageRef  referencedObject
     -->

  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="check-external-image-whitelist">
    <xsl:param name="current-whitelist"/>
    <xsl:param name="image-uri"/>
    <xsl:choose>
      <xsl:when test="string-length($current-whitelist) &gt; 0">
        <xsl:variable name="whitelist-item">
          <xsl:choose>
            <xsl:when test="contains($current-whitelist, '|')">
              <xsl:value-of select="substring-before($current-whitelist, '|')"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$current-whitelist"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:choose>
          <xsl:when test="starts-with($image-uri, $whitelist-item)">
            <br clear="all"/>
            <xsl:element name="img">
              <xsl:attribute name="src">
                <xsl:value-of select="$image-uri"/>
              </xsl:attribute>
            </xsl:element>
            <xsl:message>
<xsl:value-of select="$image-uri"/> is in the whitelist</xsl:message>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="check-external-image-whitelist">
              <xsl:with-param name="current-whitelist" select="substring-after($current-whitelist, '|')"/>
              <xsl:with-param name="image-uri" select="$image-uri"/>
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>

      </xsl:when>
      <xsl:otherwise>
        <p>WARNING: non-local image found <xsl:value-of select="$image-uri"/>. Removing. If you wish
          non-local images preserved please set the limit-external-images param to 'no'.</p>
        <xsl:message>WARNING: non-local image found <xsl:value-of select="$image-uri"/>. Removing.
          If you wish non-local images preserved please set the limit-external-images param to
          'no'.</xsl:message>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>


  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:renderMultiMedia">
    <xsl:variable name="imageRef" select="@referencedObject"/>
    <xsl:choose>
      <xsl:when test="//n1:regionOfInterest[@ID = $imageRef]">
        <!-- Here is where the Region of Interest image referencing goes -->
        <xsl:if test="             //n1:regionOfInterest[@ID = $imageRef]//n1:observationMedia/n1:value[@mediaType = 'image/gif' or             @mediaType = 'image/jpeg']">
          <xsl:variable name="image-uri" select="//n1:regionOfInterest[@ID = $imageRef]//n1:observationMedia/n1:value/n1:reference/@value"/>

          <xsl:choose>
            <xsl:when test="$limit-external-images = 'yes' and (contains($image-uri, ':') or starts-with($image-uri, '\\'))">
              <xsl:call-template name="check-external-image-whitelist">
                <xsl:with-param name="current-whitelist" select="$external-image-whitelist"/>
                <xsl:with-param name="image-uri" select="$image-uri"/>
              </xsl:call-template>
              <!--
                            <p>WARNING: non-local image found <xsl:value-of select="$image-uri"/>. Removing. If you wish non-local images preserved please set the limit-external-images param to 'no'.</p>
                            <xsl:message>WARNING: non-local image found <xsl:value-of select="$image-uri"/>. Removing. If you wish non-local images preserved please set the limit-external-images param to 'no'.</xsl:message>
                            -->
            </xsl:when>
            <!--
                        <xsl:when test="$limit-external-images='yes' and starts-with($image-uri,'\\')">
                            <p>WARNING: non-local image found <xsl:value-of select="$image-uri"/></p>
                            <xsl:message>WARNING: non-local image found <xsl:value-of select="$image-uri"/>. Removing. If you wish non-local images preserved please set the limit-external-images param to 'no'.</xsl:message>
                        </xsl:when>
                        -->
            <xsl:otherwise>
              <br clear="all"/>
              <xsl:element name="img">
                <xsl:attribute name="src">
                  <xsl:value-of select="$image-uri"/>
                </xsl:attribute>
              </xsl:element>
            </xsl:otherwise>
          </xsl:choose>

        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <!-- Here is where the direct MultiMedia image referencing goes -->
        <xsl:if test="//n1:observationMedia[@ID = $imageRef]/n1:value[@mediaType = 'image/gif' or @mediaType = 'image/jpeg']">
          <br clear="all"/>
          <xsl:element name="img">
            <xsl:attribute name="src">
              <xsl:value-of select="//n1:observationMedia[@ID = $imageRef]/n1:value/n1:reference/@value"/>
            </xsl:attribute>
          </xsl:element>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!--    Stylecode processing
     Supports Bold, Underline and Italics display
     -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="@styleCode">
    <xsl:attribute name="styleCode">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>
  <!--    Superscript or Subscript   -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:sup">
    <xsl:element name="sup">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" match="n1:sub">
    <xsl:element name="sub">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  <!-- show-signature -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-sig">
    <xsl:param name="sig"/>
    <xsl:choose>
      <xsl:when test="$sig/@code = 'S'">
        <xsl:text>signed</xsl:text>
      </xsl:when>
      <xsl:when test="$sig/@code = 'I'">
        <xsl:text>intended</xsl:text>
      </xsl:when>
      <xsl:when test="$sig/@code = 'X'">
        <xsl:text>signature required</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!--  show-id -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-id">
    <xsl:param name="id" select="."/>
    <xsl:choose>
      <xsl:when test="not($id)">
        <xsl:if test="not(@nullFlavor)">
          <xsl:if test="@extension">
            <xsl:value-of select="@extension"/>
          </xsl:if>
          <xsl:text> </xsl:text>
          <xsl:call-template name="translate-id-type">
            <xsl:with-param name="id-oid" select="@root"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="not($id/@nullFlavor)">
          <xsl:if test="$id/@extension">
            <xsl:value-of select="$id/@extension"/>
          </xsl:if>
          <xsl:text> </xsl:text>
          <xsl:call-template name="translate-id-type">
            <xsl:with-param name="id-oid" select="$id/@root"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- show-name  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-name">
    <xsl:param name="name"/>
    <xsl:choose>
      <xsl:when test="$name/n1:family">
        <xsl:if test="$name/n1:prefix">
          <xsl:value-of select="$name/n1:prefix"/>
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:value-of select="$name/n1:given"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="$name/n1:family"/>
        <xsl:if test="$name/n1:suffix">
          <xsl:text>, </xsl:text>
          <xsl:value-of select="$name/n1:suffix"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$name"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- show-gender  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-gender">
    <xsl:choose>
      <xsl:when test="@code = 'M' or @code = 'Male'">
        <xsl:text>Male</xsl:text>
      </xsl:when>
      <xsl:when test="@code = 'F' or @code = 'Female'">
        <xsl:text>Female</xsl:text>
      </xsl:when>
      <xsl:when test="@code = 'UN' or @code = 'Undifferentiated'">
        <xsl:text>Undifferentiated</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!-- show-race-ethnicity  -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-race-ethnicity">
    <xsl:choose>
      <xsl:when test="@displayName">
        <xsl:value-of select="@displayName"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="@code"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- show-contactInfo -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-contactInfo">
    <xsl:param name="contact"/>
    <xsl:call-template name="show-address">
      <xsl:with-param name="address" select="$contact/n1:addr"/>
    </xsl:call-template>
    <xsl:call-template name="show-telecom">
      <xsl:with-param name="telecom" select="$contact/n1:telecom"/>
    </xsl:call-template>
  </xsl:template>
  <!-- show-address -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-address">
    <xsl:param name="address"/>
    <div class="address-group">
      <xsl:choose>
        <xsl:when test="$address">
          <div class="adress-group-header">
            <xsl:if test="$address/@use">
              <xsl:call-template name="translateTelecomCode">
                <xsl:with-param name="code" select="$address/@use"/>
              </xsl:call-template>
            </xsl:if>
          </div>
          <div class="address-group-content">
            <p class="tight">
              <xsl:for-each select="$address/n1:streetAddressLine">
                <xsl:value-of select="."/>
                <xsl:text> </xsl:text>
              </xsl:for-each>
              <xsl:if test="$address/n1:streetName">
                <xsl:value-of select="$address/n1:streetName"/>
                <xsl:text> </xsl:text>
                <xsl:value-of select="$address/n1:houseNumber"/>
              </xsl:if>
            </p>
            <p class="tight">
              <xsl:if test="string-length($address/n1:city) &gt; 0">
                <xsl:value-of select="$address/n1:city"/>
              </xsl:if>
              <xsl:if test="string-length($address/n1:state) &gt; 0">
                <xsl:text>, </xsl:text>
                <xsl:value-of select="$address/n1:state"/>
              </xsl:if>
            </p>
            <p class="tight">
              <xsl:if test="string-length($address/n1:postalCode) &gt; 0">
                <!--<xsl:text>&#160;</xsl:text>-->
                <xsl:value-of select="$address/n1:postalCode"/>
              </xsl:if>
              <xsl:if test="string-length($address/n1:country) &gt; 0">
                <xsl:text>, </xsl:text>
                <xsl:value-of select="$address/n1:country"/>
              </xsl:if>
            </p>
          </div>

        </xsl:when>
        <xsl:otherwise>
          <div class="address-group-content">
            <span class="generated-text">
              <xsl:text>&lt;&gt;</xsl:text>
            </span>
          </div>
        </xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:template>
  <!-- show-telecom -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-telecom">
    <xsl:param name="telecom"/>
    <div class="address-group">
      <xsl:choose>
        <xsl:when test="$telecom">
          <xsl:variable name="type" select="substring-before($telecom/@value, ':')"/>
          <xsl:variable name="value" select="substring-after($telecom/@value, ':')"/>
          <xsl:if test="$type">
            <div class="address-group-header">
              <xsl:call-template name="translateTelecomCode">
                <xsl:with-param name="code" select="$type"/>
              </xsl:call-template>
              <xsl:text> : </xsl:text>
              <xsl:if test="@use">
                <xsl:text> (</xsl:text>
                <xsl:call-template name="translateTelecomCode">
                  <xsl:with-param name="code" select="@use"/>
                </xsl:call-template>
                <xsl:text>)</xsl:text>
              </xsl:if>
              <xsl:value-of select="$value"/>
            </div>
          </xsl:if>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>&lt;&gt;</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:template>
  <!-- show-recipientType -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-recipientType">
    <xsl:param name="typeCode"/>
    <xsl:choose>
      <xsl:when test="$typeCode = 'PRCP'">Primary Recipient:</xsl:when>
      <xsl:when test="$typeCode = 'TRC'">Secondary Recipient:</xsl:when>
      <xsl:otherwise>Recipient:</xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- Convert Telecom URL to display text -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="translateTelecomCode">
    <xsl:param name="code"/>
    <!--xsl:value-of select="document('voc.xml')/systems/system[@root=$code/@codeSystem]/code[@value=$code/@code]/@displayName"/-->
    <!--xsl:value-of select="document('codes.xml')/*/code[@code=$code]/@display"/-->
    <xsl:choose>
      <!-- lookup table Telecom URI -->
      <xsl:when test="$code = 'tel'">
        <xsl:text>Tel</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'fax'">
        <xsl:text>Fax</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'http'">
        <xsl:text>Web</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'mailto'">
        <xsl:text>Mail</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'H'">
        <xsl:text>Home</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'url'">
        <xsl:text>URL</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'HV'">
        <xsl:text>Vacation Home</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'HP'">
        <xsl:text>Primary Home</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'WP'">
        <xsl:text>Work Place</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'PUB'">
        <xsl:text>Pub</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'TMP'">
        <xsl:text>Temporary</xsl:text>
      </xsl:when>
      <xsl:when test="$code = 'BAD'">
        <xsl:text>Bad or Old</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>{$code='</xsl:text>
        <xsl:value-of select="$code"/>
        <xsl:text>'?}</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- convert RoleClassAssociative code to display text -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="translateRoleAssoCode">
    <xsl:param name="classCode"/>
    <xsl:param name="code"/>
    <xsl:choose>
      <xsl:when test="$classCode = 'AFFL'">
        <xsl:text>affiliate</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'AGNT'">
        <xsl:text>agent</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'ASSIGNED'">
        <xsl:text>assigned entity</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'COMPAR'">
        <xsl:text>commissioning party</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'CON'">
        <xsl:text>contact</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'ECON'">
        <xsl:text>emergency contact</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'NOK'">
        <xsl:text>next of kin</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'SGNOFF'">
        <xsl:text>signing authority</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'GUARD'">
        <xsl:text>guardian</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'GUAR'">
        <xsl:text>guardian</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'CIT'">
        <xsl:text>citizen</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'COVPTY'">
        <xsl:text>covered party</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'PRS'">
        <xsl:text>personal relationship</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'CAREGIVER'">
        <xsl:text>care giver</xsl:text>
      </xsl:when>
      <xsl:when test="$classCode = 'PROV'">
        <xsl:text>healthcare provider</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>{$classCode='</xsl:text>
        <xsl:value-of select="$classCode"/>
        <xsl:text>'?}</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="($code/@code) and ($code/@codeSystem = '2.16.840.1.113883.5.111')">
      <xsl:text> </xsl:text>
      <xsl:choose>
        <xsl:when test="$code/@code = 'FTH'">
          <xsl:text>(Father)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'MTH'">
          <xsl:text>(Mother)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'NPRN'">
          <xsl:text>(Natural parent)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'STPPRN'">
          <xsl:text>(Step parent)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'SONC'">
          <xsl:text>(Son)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'DAUC'">
          <xsl:text>(Daughter)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'CHILD'">
          <xsl:text>(Child)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'EXT'">
          <xsl:text>(Extended family member)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'NBOR'">
          <xsl:text>(Neighbor)</xsl:text>
        </xsl:when>
        <xsl:when test="$code/@code = 'SIGOTHR'">
          <xsl:text>(Significant other)</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>{$code/@code='</xsl:text>
          <xsl:value-of select="$code/@code"/>
          <xsl:text>'?}</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>
  </xsl:template>
  <!-- show time -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-time">
    <xsl:param name="datetime"/>
    <xsl:choose>
      <xsl:when test="not($datetime)">
        <xsl:call-template name="formatDateTime">
          <xsl:with-param name="date" select="@value"/>
        </xsl:call-template>
        <xsl:text> </xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="formatDateTime">
          <xsl:with-param name="date" select="$datetime/@value"/>
        </xsl:call-template>
        <xsl:text> </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- paticipant facility and date -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="facilityAndDates">
    <table class="header_table">
      <tbody>
        <!-- facility id -->
        <tr>
          <td class="td_header_role_name">
            <span class="td_label">
              <xsl:text>Facility ID</xsl:text>
            </span>
          </td>
          <td class="td_header_role_value">
            <xsl:choose>
              <xsl:when test="                   count(/n1:ClinicalDocument/n1:participant                   [@typeCode = 'LOC'][@contextControlCode = 'OP']                   /n1:associatedEntity[@classCode = 'SDLOC']/n1:id) &gt; 0">
                <!-- change context node -->
                <xsl:for-each select="                     /n1:ClinicalDocument/n1:participant                     [@typeCode = 'LOC'][@contextControlCode = 'OP']                     /n1:associatedEntity[@classCode = 'SDLOC']/n1:id">
                  <xsl:call-template name="show-id"/>
                  <!-- change context node again, for the code -->
                  <xsl:for-each select="../n1:code">
                    <xsl:text> (</xsl:text>
                    <xsl:call-template name="show-code">
                      <xsl:with-param name="code" select="."/>
                    </xsl:call-template>
                    <xsl:text>)</xsl:text>
                  </xsl:for-each>
                </xsl:for-each>
              </xsl:when>
              <xsl:otherwise> Not available </xsl:otherwise>
            </xsl:choose>
          </td>
        </tr>
        <!-- Period reported -->
        <tr>
          <td class="td_header_role_name">
            <span class="td_label">
              <xsl:text>First day of period reported</xsl:text>
            </span>
          </td>
          <td class="td_header_role_value">
            <xsl:call-template name="show-time">
              <xsl:with-param name="datetime" select="                   /n1:ClinicalDocument/n1:documentationOf                   /n1:serviceEvent/n1:effectiveTime/n1:low"/>
            </xsl:call-template>
          </td>
        </tr>
        <tr>
          <td class="td_header_role_name">
            <span class="td_label">
              <xsl:text>Last day of period reported</xsl:text>
            </span>
          </td>
          <td class="td_header_role_value">
            <xsl:call-template name="show-time">
              <xsl:with-param name="datetime" select="                   /n1:ClinicalDocument/n1:documentationOf                   /n1:serviceEvent/n1:effectiveTime/n1:high"/>
            </xsl:call-template>
          </td>
        </tr>
      </tbody>
    </table>
  </xsl:template>
  <!-- show assignedEntity -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-assignedEntity">
    <xsl:param name="asgnEntity"/>
    <xsl:choose>
      <xsl:when test="$asgnEntity/n1:assignedPerson/n1:name">
        <xsl:call-template name="show-name">
          <xsl:with-param name="name" select="$asgnEntity/n1:assignedPerson/n1:name"/>
        </xsl:call-template>
        <xsl:if test="$asgnEntity/n1:representedOrganization/n1:name">
          <xsl:text> of </xsl:text>
          <xsl:value-of select="$asgnEntity/n1:representedOrganization/n1:name"/>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$asgnEntity/n1:representedOrganization">
        <xsl:value-of select="$asgnEntity/n1:representedOrganization/n1:name"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="$asgnEntity/n1:id">
          <xsl:call-template name="show-id"/>
          <xsl:choose>
            <xsl:when test="position() != last()">
              <xsl:text>, </xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <br/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- show relatedEntity -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-relatedEntity">
    <xsl:param name="relatedEntity"/>
    <xsl:choose>
      <xsl:when test="$relatedEntity/n1:relatedPerson/n1:name">
        <xsl:call-template name="show-name">
          <xsl:with-param name="name" select="$relatedEntity/n1:relatedPerson/n1:name"/>
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!-- show associatedEntity -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-associatedEntity">
    <xsl:param name="assoEntity"/>
    <xsl:choose>
      <xsl:when test="$assoEntity/n1:associatedPerson">
        <xsl:for-each select="$assoEntity/n1:associatedPerson/n1:name">
          <xsl:call-template name="show-name">
            <xsl:with-param name="name" select="."/>
          </xsl:call-template>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$assoEntity/n1:scopingOrganization">
        <xsl:for-each select="$assoEntity/n1:scopingOrganization">
          <xsl:if test="n1:name">
            <xsl:call-template name="show-name">
              <xsl:with-param name="name" select="n1:name"/>
            </xsl:call-template>
            <br/>
          </xsl:if>
          <xsl:if test="n1:standardIndustryClassCode">
            <xsl:value-of select="n1:standardIndustryClassCode/@displayName"/>
            <xsl:text> code:</xsl:text>
            <xsl:value-of select="n1:standardIndustryClassCode/@code"/>
          </xsl:if>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$assoEntity/n1:code">
        <xsl:call-template name="show-code">
          <xsl:with-param name="code" select="$assoEntity/n1:code"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$assoEntity/n1:id">
        <xsl:value-of select="$assoEntity/n1:id/@extension"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="$assoEntity/n1:id/@root"/>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!-- show code
     if originalText present, return it, otherwise, check and return attribute: display name
     -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-code">
    <xsl:param name="code"/>
    <xsl:variable name="this-codeSystem">
      <xsl:value-of select="$code/@codeSystem"/>
    </xsl:variable>
    <xsl:variable name="this-code">
      <xsl:value-of select="$code/@code"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="$code/n1:originalText">
        <xsl:value-of select="$code/n1:originalText"/>
      </xsl:when>
      <xsl:when test="$code/@displayName">
        <xsl:value-of select="$code/@displayName"/>
      </xsl:when>

      <xsl:otherwise>
        <xsl:value-of select="$this-code"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- show classCode -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-actClassCode">
    <xsl:param name="clsCode"/>
    <xsl:choose>
      <xsl:when test="$clsCode = 'ACT'">
        <xsl:text>healthcare service</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'ACCM'">
        <xsl:text>accommodation</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'ACCT'">
        <xsl:text>account</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'ACSN'">
        <xsl:text>accession</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'ADJUD'">
        <xsl:text>financial adjudication</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'CONS'">
        <xsl:text>consent</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'CONTREG'">
        <xsl:text>container registration</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'CTTEVENT'">
        <xsl:text>clinical trial timepoint event</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'DISPACT'">
        <xsl:text>disciplinary action</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'ENC'">
        <xsl:text>encounter</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'INC'">
        <xsl:text>incident</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'INFRM'">
        <xsl:text>inform</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'INVE'">
        <xsl:text>invoice element</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'LIST'">
        <xsl:text>working list</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'MPROT'">
        <xsl:text>monitoring program</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'PCPR'">
        <xsl:text>care provision</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'PROC'">
        <xsl:text>procedure</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'REG'">
        <xsl:text>registration</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'REV'">
        <xsl:text>review</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'SBADM'">
        <xsl:text>substance administration</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'SPCTRT'">
        <xsl:text>speciment treatment</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'SUBST'">
        <xsl:text>substitution</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'TRNS'">
        <xsl:text>transportation</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'VERIF'">
        <xsl:text>verification</xsl:text>
      </xsl:when>
      <xsl:when test="$clsCode = 'XACT'">
        <xsl:text>financial transaction</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!-- show participationType -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-participationType">
    <xsl:param name="ptype"/>
    <xsl:choose>
      <xsl:when test="$ptype = 'PPRF'">
        <xsl:text>primary performer</xsl:text>
      </xsl:when>
      <xsl:when test="$ptype = 'PRF'">
        <xsl:text>performer</xsl:text>
      </xsl:when>
      <xsl:when test="$ptype = 'VRF'">
        <xsl:text>verifier</xsl:text>
      </xsl:when>
      <xsl:when test="$ptype = 'SPRF'">
        <xsl:text>secondary performer</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <!-- show participationFunction -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-participationFunction">
    <xsl:param name="pFunction"/>
    <xsl:choose>
      <!-- From the HL7 v3 ParticipationFunction code system -->
      <xsl:when test="$pFunction = 'ADMPHYS'">
        <xsl:text>(admitting physician)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'ANEST'">
        <xsl:text>(anesthesist)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'ANRS'">
        <xsl:text>(anesthesia nurse)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'ATTPHYS'">
        <xsl:text>(attending physician)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'DISPHYS'">
        <xsl:text>(discharging physician)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'FASST'">
        <xsl:text>(first assistant surgeon)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'MDWF'">
        <xsl:text>(midwife)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'NASST'">
        <xsl:text>(nurse assistant)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'PCP'">
        <xsl:text>(primary care physician)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'PRISURG'">
        <xsl:text>(primary surgeon)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'RNDPHYS'">
        <xsl:text>(rounding physician)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'SASST'">
        <xsl:text>(second assistant surgeon)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'SNRS'">
        <xsl:text>(scrub nurse)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'TASST'">
        <xsl:text>(third assistant)</xsl:text>
      </xsl:when>
      <!-- From the HL7 v2 Provider Role code system (2.16.840.1.113883.12.443) which is used by HITSP -->
      <xsl:when test="$pFunction = 'CP'">
        <xsl:text>(consulting provider)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'PP'">
        <xsl:text>(primary care provider)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'RP'">
        <xsl:text>(referring provider)</xsl:text>
      </xsl:when>
      <xsl:when test="$pFunction = 'MP'">
        <xsl:text>(medical home provider)</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="formatDateTime">
    <xsl:param name="date"/>
    <!-- month -->
    <xsl:variable name="month" select="substring($date, 5, 2)"/>
    <!-- day -->
    <xsl:value-of select="$month"/>
    <xsl:text>/</xsl:text>
    <xsl:choose>
      <xsl:when test="substring($date, 7, 1) = &quot;0&quot;">
        <xsl:value-of select="substring($date, 8, 1)"/>
        <xsl:text>/</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="substring($date, 7, 2)"/>
        <xsl:text>/</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <!-- year -->
    <xsl:value-of select="substring($date, 1, 4)"/>
    <!-- time and US timezone -->
    <xsl:if test="string-length($date) &gt; 8">
      <!-- time -->
      <xsl:variable name="time">
        <xsl:value-of select="substring($date, 9, 6)"/>
      </xsl:variable>
      <xsl:variable name="hh">
        <xsl:value-of select="substring($time, 1, 2)"/>
      </xsl:variable>
      <xsl:variable name="mm">
        <xsl:value-of select="substring($time, 3, 2)"/>
      </xsl:variable>
      <xsl:variable name="ss">
        <xsl:value-of select="substring($time, 5, 2)"/>
      </xsl:variable>
      <xsl:if test="(string-length($hh) &gt; 1 and not($hh = '00')) or (string-length($mm) &gt; 1 and not($mm = '00'))">
        <xsl:text>, </xsl:text>
        <xsl:value-of select="$hh"/>
        <xsl:if test="string-length($mm) &gt; 1 and not(contains($mm, '-')) and not(contains($mm, '+'))">
          <xsl:text>:</xsl:text>
          <xsl:value-of select="$mm"/>
        </xsl:if>
      </xsl:if>
    </xsl:if>
  </xsl:template>
  <!-- convert to lower case -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="caseDown">
    <xsl:param name="data"/>
    <xsl:if test="$data">
      <xsl:value-of select="translate($data, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')"/>
    </xsl:if>
  </xsl:template>
  <!-- convert to upper case -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="caseUp">
    <xsl:param name="data"/>
    <xsl:if test="$data">
      <xsl:value-of select="translate($data, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
    </xsl:if>
  </xsl:template>
  <!-- convert first character to upper case -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="firstCharCaseUp">
    <xsl:param name="data"/>
    <xsl:if test="$data">
      <xsl:call-template name="caseUp">
        <xsl:with-param name="data" select="substring($data, 1, 1)"/>
      </xsl:call-template>
      <xsl:value-of select="substring($data, 2)"/>
    </xsl:if>
  </xsl:template>
  <!-- show-noneFlavor -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="show-noneFlavor">
    <xsl:param name="nf"/>
    <xsl:choose>
      <xsl:when test="$nf = 'NI'">
        <xsl:text>no information</xsl:text>
      </xsl:when>
      <xsl:when test="$nf = 'INV'">
        <xsl:text>invalid</xsl:text>
      </xsl:when>
      <xsl:when test="$nf = 'MSK'">
        <xsl:text>masked</xsl:text>
      </xsl:when>
      <xsl:when test="$nf = 'NA'">
        <xsl:text>not applicable</xsl:text>
      </xsl:when>
      <xsl:when test="$nf = 'UNK'">
        <xsl:text>unknown</xsl:text>
      </xsl:when>
      <xsl:when test="$nf = 'OTH'">
        <xsl:text>other</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <!-- convert common OIDs for Identifiers -->
  <xsl:template xmlns:n1="urn:hl7-org:v3" xmlns:in="urn:lantana-com:inline-variable-data" name="translate-id-type">
    <xsl:param name="id-oid"/>
    <xsl:choose>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.1'">
        <xsl:text>United States Social Security Number</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.6'">
        <xsl:text>United States National Provider Identifier</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.2'">
        <xsl:text>Alaska Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.1'">
        <xsl:text>Alabama Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.5'">
        <xsl:text>Arkansas Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.4'">
        <xsl:text>Arizona Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.6'">
        <xsl:text>California Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.8'">
        <xsl:text>Colorado Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.9'">
        <xsl:text>Connecticut Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.11'">
        <xsl:text>DC Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.10'">
        <xsl:text>Delaware Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.12'">
        <xsl:text>Florida Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.13'">
        <xsl:text>Georgia Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.15'">
        <xsl:text>Hawaii Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.18'">
        <xsl:text>Indiana Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.19'">
        <xsl:text>Iowa Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.16'">
        <xsl:text>Idaho Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.17'">
        <xsl:text>Illinois Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.20'">
        <xsl:text>Kansas Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.21'">
        <xsl:text>Kentucky Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.22'">
        <xsl:text>Louisiana Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.25'">
        <xsl:text>Massachusetts Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.24'">
        <xsl:text>Maryland Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.23'">
        <xsl:text>Maine Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.26'">
        <xsl:text>Michigan Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.27'">
        <xsl:text>Minnesota Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.29'">
        <xsl:text>Missouri Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.28'">
        <xsl:text>Mississippi Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.30'">
        <xsl:text>Montana Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.36'">
        <xsl:text>New York Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.37'">
        <xsl:text>North Carolina Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.38'">
        <xsl:text>North Dakota Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.31'">
        <xsl:text>Nebraska Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.33'">
        <xsl:text>New Hampshire Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.34'">
        <xsl:text>New Jersey Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.35'">
        <xsl:text>New Mexico Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.32'">
        <xsl:text>Nevada Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.39'">
        <xsl:text>Ohio Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.40'">
        <xsl:text>Oklahoma Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.41'">
        <xsl:text>Oregon Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.42'">
        <xsl:text>Pennsylvania Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.44'">
        <xsl:text>Rhode Island Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.45'">
        <xsl:text>South Carolina Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.46'">
        <xsl:text>South Dakota Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.47'">
        <xsl:text>Tennessee Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.48'">
        <xsl:text>Texas Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.49'">
        <xsl:text>Utah Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.51'">
        <xsl:text>Virginia Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.50'">
        <xsl:text>Vermont Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.53'">
        <xsl:text>Washington Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.55'">
        <xsl:text>Wisconsin Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.54'">
        <xsl:text>West Virginia Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.4.3.56'">
        <xsl:text>Wyoming Driver's License</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.12.203'">
        <xsl:text>Identifier Type (HL7)</xsl:text>
      </xsl:when>

      <!-- Axesson-specific OIDs -->
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.1'">
        <xsl:text>Associated Pathology Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.2'">
        <xsl:text>ATMS</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.3'">
        <xsl:text>AXESSON TRANSCRIPTION</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.4'">
        <xsl:text>Axesson Word Doc Transcriptions</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.5'">
        <xsl:text>CrossTx</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.9'">
        <xsl:text>Dignity Health Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.9.4.1'">
        <xsl:text>Dignity Boulder Creek</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.10'">
        <xsl:text>Dominican Santa Cruz Hospital</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.10.4.1'">
        <xsl:text>Dignity Internal Medicine</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.10.4.2'">
        <xsl:text>Dignity Pediatrics</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50023'">
        <xsl:text>Joydip Bhattacharya</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50003'">
        <xsl:text>Balance Health of Ben Lomond</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50040'">
        <xsl:text>Edward T Bradbury MD A Prof. Corp</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50014'">
        <xsl:text>Bayview Gastroenterology</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50037'">
        <xsl:text>Peggy Chen, M.D.</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50004'">
        <xsl:text>Central Coast Sleep Disorders Center</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50024'">
        <xsl:text>Central Coast Oncology and Hematology</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50002'">
        <xsl:text>Albert Crevello, MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50021'">
        <xsl:text>Diabetes Health Center</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50005'">
        <xsl:text>Foot Doctors of Santa Cruz</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50032'">
        <xsl:text>Maria Granthom, M.D.</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50006'">
        <xsl:text>Gastroenterology</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50030'">
        <xsl:text>Harbor Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50033'">
        <xsl:text>Monterey Bay Gastroenterology</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50034'">
        <xsl:text>Monterey Bay Urology</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '1.2.840.114398.1.35.1'">
        <xsl:text>No More Clipboard</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50010'">
        <xsl:text>Plazita Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50009'">
        <xsl:text>Pajaro Valley Neurolgy Medical Associates</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50007'">
        <xsl:text>Milan Patel, MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50039'">
        <xsl:text>Santa Cruz Pulmonary Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50038'">
        <xsl:text>Rio Del Mar Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50011'">
        <xsl:text>Romo, Mary-Lou</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50027'">
        <xsl:text>Santa Cruz Office Santa Cruz Ear Nose and Throat Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50012'">
        <xsl:text>Scotts Valley Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50041'">
        <xsl:text>Simkin, Josefa MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.3.1.50013'">
        <xsl:text>Vu, Thanh</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.6'">
        <xsl:text>Bioreference Labs</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.7'">
        <xsl:text>BSCA Claims Data</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.8'">
        <xsl:text>CCSDC</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.11'">
        <xsl:text>Cedar Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.12'">
        <xsl:text>Cedar Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.13'">
        <xsl:text>DIANON</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.14'">
        <xsl:text>ANDREA EDWARDS MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.15'">
        <xsl:text>Elysium</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.16'">
        <xsl:text>Family Doctors of Santa Cruz</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.17'">
        <xsl:text>Hurray, Alvie</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.18'">
        <xsl:text>Hunter</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.19'">
        <xsl:text>LABCORP</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.20'">
        <xsl:text>LABCORP UNKNOWN</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.21'">
        <xsl:text>Melissa Lopez-Bermejo, MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.22'">
        <xsl:text>Monterey Bay Family Physicians</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.23'">
        <xsl:text>Medtek</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.24'">
        <xsl:text>Mirth Support Testing Facility</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.25'">
        <xsl:text>NSIGHT</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.26'">
        <xsl:text>NwHIN</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.27'">
        <xsl:text>OrthoNorCal</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.28'">
        <xsl:text>Pajaro Health Center</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.29'">
        <xsl:text>Pajaro Valley Medical Clinic</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.30'">
        <xsl:text>Pajaro Valley Personal Health</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.31'">
        <xsl:text>PMG</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.32'">
        <xsl:text>QUEST</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.33'">
        <xsl:text>Radiology Medical Group</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.34'">
        <xsl:text>Resneck-Sannes, L. David MD</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.35'">
        <xsl:text>Salud Para La Gente</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.36'">
        <xsl:text>SBWTest</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.37'">
        <xsl:text>Quest Diagnostics SC</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.38'">
        <xsl:text>Santa Cruz County Health Services Agency</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.39'">
        <xsl:text>Santa Cruz County Mental Health</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.40'">
        <xsl:text>SCHIEAUTH</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.41'">
        <xsl:text>Santa Cruz HIE</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.42'">
        <xsl:text>Santa Cruz Nephrology Medical Group, Inc</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.43'">
        <xsl:text>Santa Cruz Surgery Center</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.44'">
        <xsl:text>Quest Diagnostics SJ</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.45'">
        <xsl:text>Stanford Lab</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.46'">
        <xsl:text>Unknown</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.47'">
        <xsl:text>Watsonville Community Hospital</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.3.290.2.1.48'">
        <xsl:text>zzBAD_REFERENCE_FACILITY</xsl:text>
      </xsl:when>



      <!-- Example OIDS -->
      <xsl:when test="$id-oid = '2.16.840.1.113883.19.5'">
        <xsl:text>Meaningless identifier, not to be used for any actual entities. Examples only.</xsl:text>
      </xsl:when>
      <xsl:when test="$id-oid = '2.16.840.1.113883.19.5.99999.2'">
        <xsl:text>Meaningless identifier, not to be used for any actual entities. Examples only.</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>OID: </xsl:text>
        <xsl:value-of select="$id-oid"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="lantana-css">
    <style>
      /* Catch all for the document */
      .cda-render{
          font-family:CenturyGothic, sans-serif;
          /*font-size:1.25em;*/
      }

      /* One-off - CDA Document Title */
      .cda-render h1.cda-title{
        color:#b3623d;
        font-size:1.5em;
        font-weight:bold;
        text-align:center;
        text-transform: uppercase;
      }


      /* One-off - Table of contents formatting */
      .cda-render .toc-header-container {
        padding-top:0.5em;
        border-bottom-width:0.1em;
        border-bottom-style:solid;
        border-bottom-color:#b3623d;
        padding-bottom:0.5em;
      }

      .cda-render .toc-header {
        text-transform:uppercase;
        color:#b3623d;
        font-weight:bold;
      }

      .cda-render .toc {
        margin-top:3em;
        padding: 0px 15px;
      }

      .cda-render .toc-box {

      }


      /* One-off - Patient Name Formatting */
      .cda-render .patient-name {
        color:#336b7a;
        font-size:1.25em;
        font-weight:bold;
      }

     /* Patient ID Formatting */
     .patient-id {
       border-left-width: 0.15em;
       border-left-style: solid;
       border-left-color: #478B95;
     }
      /* Re-usable - Section-Title */
      .cda-render .section-title {
        color:#336b7a;
        font-size:1.09em;
        font-weight:bold;
        text-transform: uppercase;
      }

      /* Re-usable - Attribute title */
      .cda-render .attribute-title {
        color:#000000;
        font-weight:bold;
        font-size:1.04em;
      }


      /***** Header Grouping */
      .cda-render .header{
          border-bottom-width:0.1em;
          border-bottom-style:solid;
          border-bottom-color:#1B6373;
          padding-bottom:0.5em;
      }

      .cda-render .header-group-content{
          margin-left:1em;
          padding-left:0.5em;
          border-left-width:0.15em;
          border-left-style:solid;
          border-left-color:#478B95;
      }

      .cda-render .tight{
          margin:0;
      }
      .cda-render .generated-text{
          white-space:no-wrap;
          margin:0em;
          color:#B0592C;
          font-style:italic;
      }
      .cda-render .bottom{
          border-top-width:0.2em;
          border-top-color:#B0592C;
          border-top-style:solid;
      }

      /***** Table of Contents Attributes */
      /* Table of contents entry */
      .cda-render .lantana-toc {
        text-transform: uppercase;
      }

      .cda-render .bold {
        font-weight: bold;
      }

      .cda-render .active {
        border-right-color: #336b7a;
        border-right-style: solid;
        border-left-color: #336b7a;
        border-left-style: solid;
        background-color:#eee;
      }

      #navbar-list-cda {
        overflow: auto;
      }
    </style>
  </xsl:template>
<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="lantana-js">
    <script type="text/javascript">

$(document).ready(function(){
    $('#navbar-list-cda').height($(window).height()-100);
});
$(window).resize(function(){
    $('#navbar-list-cda').height($(window).height()-100);
});

$(document).ready(function(){
    $('#navbar-list-cda').height($(window).height()-100);
});

$(window).resize(function(){
    $('#navbar-list-cda').height($(window).height()-100);
});

$(document).ready(function(){
    $('.cda-render a[href*="#"]:not([href="#"])').bind('click.smoothscroll',function (e) {
        e.preventDefault();

        var target = this.hash,
        $target = $(target);

        $('html, body').stop().animate({
            'scrollTop': $target.offset().top
        }, 1000, 'swing', function () {
            window.location.hash = target;

            // lets add a div in the background
            $('&lt;div /&gt;').css({'background':'#336b7a'}).prependTo($target).fadeIn('fast', function(){
                $(this).fadeOut('fast', function(){
                    $(this).remove();
                });
            });

        });
    });
});

$( function() {
    $( "#navbar-list-cda-sortable" ).sortable();
    $( "#navbar-list-cda-sortable" ).disableSelection();
  } );

  $( function( ) {
    var $nav = $( '#navbar-list-cda-sortable' );
    var $content = $( '#doc-clinical-info' );
    var $originalContent = $content.clone( );
    $nav.sortable( {
        update: function ( e ) {
            $content.empty( );
            $nav.find( 'a' ).each( function ( ) {
                $content.append( $originalContent.clone( ).find( $( this ).attr( 'href' ) ).parent ( ) );
            } );

              $('[data-spy="scroll"]').each(function () {
  var $spy = $(this).scrollspy('refresh')
})
        }
    } );
  } );



    </script>
  </xsl:template>
<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="jquery">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"/>

    </xsl:template>
<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="jquery-ui">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"/>
    </xsl:template>
<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="bootstrap-css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css"/>
    </xsl:template>
<xsl:template xmlns:xs="http://www.w3.org/2001/XMLSchema" name="bootstrap-javascript">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"/>
    </xsl:template>
</xsl:stylesheet>
