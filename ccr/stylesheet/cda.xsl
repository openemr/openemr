<?xml version="1.0" encoding="UTF-8"?>
<!--
  Title: CDA XSL StyleSheet
  Original Filename: cda.xsl
  Version: 3.0
  Revision History: 08/12/08 Jingdong Li updated
  Revision History: 12/11/09 KH updated
  Revision History:  03/30/10 Jingdong Li updated.
  Revision History:  08/25/10 Jingdong Li updated
  Revision History:  09/17/10 Jingdong Li updated
  Revision History:  01/05/11 Jingdong Li updated
  Specification: ANSI/HL7 CDAR2
  The current version and documentation are available at http://www.lantanagroup.com/resources/tools/.
  We welcome feedback and contributions to tools@lantanagroup.com
  The stylesheet is the cumulative work of several developers; the most significant prior milestones were the foundation work from HL7
  Germany and Finland (Tyylitiedosto) and HL7 US (Calvin Beebe), and the presentation approach from Tony Schaller, medshare GmbH provided at IHIC 2009.
-->
<!-- LICENSE INFORMATION
  Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License.
  You may obtain a copy of the License at  http://www.apache.org/licenses/LICENSE-2.0
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:n1="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   <xsl:output method="html" indent="yes" version="4.01" encoding="ISO-8859-1" doctype-system="http://www.w3.org/TR/html4/strict.dtd" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
   <!-- global variable title -->
   <xsl:variable name="title">
      <xsl:choose>
         <xsl:when test="string-length(/n1:ClinicalDocument/n1:title)  &gt;= 1">
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
   <xsl:template match="/">
      <xsl:apply-templates select="n1:ClinicalDocument"/>
   </xsl:template>
   <!-- produce browser rendered, human readable clinical document -->
   <xsl:template match="n1:ClinicalDocument">
      <html>
         <head>
            <xsl:comment> Do NOT edit this HTML directly: it was generated via an XSLT transformation from a CDA Release 2 XML document. </xsl:comment>
            <title>
               <xsl:value-of select="$title"/>
            </title>
            <xsl:call-template name="addCSS"/>
         </head>
         <body>
            <h1 class="h1center">
               <xsl:value-of select="$title"/>
            </h1>
            <!-- START display top portion of clinical document -->
            <xsl:call-template name="recordTarget"/>
            <xsl:call-template name="documentGeneral"/>
            <xsl:call-template name="documentationOf"/>
            <xsl:call-template name="author"/>
            <xsl:call-template name="componentof"/>
            <xsl:call-template name="participant"/>
            <xsl:call-template name="dataEnterer"/>
            <xsl:call-template name="authenticator"/>
            <xsl:call-template name="informant"/>
            <xsl:call-template name="informationRecipient"/>
            <xsl:call-template name="legalAuthenticator"/>
            <xsl:call-template name="custodian"/>
            <!-- END display top portion of clinical document -->
            <!-- produce table of contents -->
            <xsl:if test="not(//n1:nonXMLBody)">
               <xsl:if test="count(/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component[n1:section]) &gt; 1">
                  <xsl:call-template name="make-tableofcontents"/>
               </xsl:if>
            </xsl:if>
            <hr align="left" color="teal" size="2" width="80%"/>
            <!-- produce human readable document content -->
            <xsl:apply-templates select="n1:component/n1:structuredBody|n1:component/n1:nonXMLBody"/>
            <br/>
            <br/>
         </body>
      </html>
   </xsl:template>
   <!-- generate table of contents -->
   <xsl:template name="make-tableofcontents">
      <h2>
         <a name="toc">Table of Contents</a>
      </h2>
      <ul>
         <xsl:for-each select="n1:component/n1:structuredBody/n1:component/n1:section/n1:title">
            <li>
               <a href="#{generate-id(.)}">
                  <xsl:value-of select="."/>
               </a>
            </li>
         </xsl:for-each>
      </ul>
   </xsl:template>
   <!-- header elements -->
   <xsl:template name="documentGeneral">
      <table class="header_table">
         <tbody>
            <tr>
               <td width="20%" bgcolor="#3399ff">
                  <span class="td_label">
                     <xsl:text>Document Id</xsl:text>
                  </span>
               </td>
               <td width="80%">
                  <xsl:call-template name="show-id">
                     <xsl:with-param name="id" select="n1:id"/>
                  </xsl:call-template>
               </td>
            </tr>
            <tr>
               <td width="20%" bgcolor="#3399ff">
                  <span class="td_label">
                     <xsl:text>Document Created:</xsl:text>
                  </span>
               </td>
               <td width="80%">
                  <xsl:call-template name="show-time">
                     <xsl:with-param name="datetime" select="n1:effectiveTime"/>
                  </xsl:call-template>
               </td>
            </tr>
         </tbody>
      </table>
   </xsl:template>
   <!-- confidentiality -->
   <xsl:template name="confidentiality">
      <table class="header_table">
         <tbody>
            <td width="20%" bgcolor="#3399ff">
               <xsl:text>Confidentiality</xsl:text>
            </td>
            <td width="80%">
               <xsl:choose>
                  <xsl:when test="n1:confidentialityCode/@code  = &apos;N&apos;">
                     <xsl:text>Normal</xsl:text>
                  </xsl:when>
                  <xsl:when test="n1:confidentialityCode/@code  = &apos;R&apos;">
                     <xsl:text>Restricted</xsl:text>
                  </xsl:when>
                  <xsl:when test="n1:confidentialityCode/@code  = &apos;V&apos;">
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
   <xsl:template name="author">
      <xsl:if test="n1:author">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:author/n1:assignedAuthor">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Author</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
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
                              <xsl:value-of select="n1:assignedAuthoringDevice/n1:softwareName"/>
                           </xsl:when>
                           <xsl:when test="n1:representedOrganization">
                              <xsl:call-template name="show-name">
                                 <xsl:with-param name="name" select="n1:representedOrganization/n1:name"/>
                              </xsl:call-template>
                           </xsl:when>
                           <xsl:otherwise>
                              <xsl:for-each select="n1:id">
                                 <xsl:call-template name="show-id"/>
                                 <br/>
                              </xsl:for-each>
                           </xsl:otherwise>
                        </xsl:choose>
                     </td>
                  </tr>
                  <xsl:if test="n1:addr | n1:telecom">
                     <tr>
                        <td bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Contact info</xsl:text>
                           </span>
                        </td>
                        <td>
                           <xsl:call-template name="show-contactInfo">
                              <xsl:with-param name="contact" select="."/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:if>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!--  authenticator -->
   <xsl:template name="authenticator">
      <xsl:if test="n1:authenticator">
         <table class="header_table">
            <tbody>
               <tr>
                  <xsl:for-each select="n1:authenticator">
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Signed </xsl:text>
                           </span>
                        </td>
                        <td width="80%">
                           <xsl:call-template name="show-name">
                              <xsl:with-param name="name" select="n1:assignedEntity/n1:assignedPerson/n1:name"/>
                           </xsl:call-template>
                           <xsl:text> at </xsl:text>
                           <xsl:call-template name="show-time">
                              <xsl:with-param name="date" select="n1:time"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                     <xsl:if test="n1:assignedEntity/n1:addr | n1:assignedEntity/n1:telecom">
                        <tr>
                           <td bgcolor="#3399ff">
                              <span class="td_label">
                                 <xsl:text>Contact info</xsl:text>
                              </span>
                           </td>
                           <td width="80%">
                              <xsl:call-template name="show-contactInfo">
                                 <xsl:with-param name="contact" select="n1:assignedEntity"/>
                              </xsl:call-template>
                           </td>
                        </tr>
                     </xsl:if>
                  </xsl:for-each>
               </tr>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- legalAuthenticator -->
   <xsl:template name="legalAuthenticator">
      <xsl:if test="n1:legalAuthenticator">
         <table class="header_table">
            <tbody>
               <tr>
                  <td width="20%" bgcolor="#3399ff">
                     <span class="td_label">
                        <xsl:text>Legal authenticator</xsl:text>
                     </span>
                  </td>
                  <td width="80%">
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
                  </td>
               </tr>
               <xsl:if test="n1:legalAuthenticator/n1:assignedEntity/n1:addr | n1:legalAuthenticator/n1:assignedEntity/n1:telecom">
                  <tr>
                     <td bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Contact info</xsl:text>
                        </span>
                     </td>
                     <td>
                        <xsl:call-template name="show-contactInfo">
                           <xsl:with-param name="contact" select="n1:legalAuthenticator/n1:assignedEntity"/>
                        </xsl:call-template>
                     </td>
                  </tr>
               </xsl:if>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- dataEnterer -->
   <xsl:template name="dataEnterer">
      <xsl:if test="n1:dataEnterer">
         <table class="header_table">
            <tbody>
               <tr>
                  <td width="20%" bgcolor="#3399ff">
                     <span class="td_label">
                        <xsl:text>Entered by</xsl:text>
                     </span>
                  </td>
                  <td width="80%">
                     <xsl:call-template name="show-assignedEntity">
                        <xsl:with-param name="asgnEntity" select="n1:dataEnterer/n1:assignedEntity"/>
                     </xsl:call-template>
                  </td>
               </tr>
               <xsl:if test="n1:dataEnterer/n1:assignedEntity/n1:addr | n1:dataEnterer/n1:assignedEntity/n1:telecom">
                  <tr>
                     <td bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Contact info</xsl:text>
                        </span>
                     </td>
                     <td>
                        <xsl:call-template name="show-contactInfo">
                           <xsl:with-param name="contact" select="n1:dataEnterer/n1:assignedEntity"/>
                        </xsl:call-template>
                     </td>
                  </tr>
               </xsl:if>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- componentOf -->
   <xsl:template name="componentof">
      <xsl:if test="n1:componentOf">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:componentOf/n1:encompassingEncounter">
                  <xsl:if test="n1:id">
                     <tr>
                        <xsl:choose>
                           <xsl:when test="n1:code">
                              <td width="20%" bgcolor="#3399ff">
                                 <span class="td_label">
                                    <xsl:text>Encounter Id</xsl:text>
                                 </span>
                              </td>
                              <td width="30%">
                                 <xsl:call-template name="show-id">
                                    <xsl:with-param name="id" select="n1:id"/>
                                 </xsl:call-template>
                              </td>
                              <td width="15%" bgcolor="#3399ff">
                                 <span class="td_label">
                                    <xsl:text>Encounter Type</xsl:text>
                                 </span>
                              </td>
                              <td>
                                 <xsl:call-template name="show-code">
                                    <xsl:with-param name="code" select="n1:code"/>
                                 </xsl:call-template>
                              </td>
                           </xsl:when>
                           <xsl:otherwise>
                              <td width="20%" bgcolor="#3399ff">
                                 <span class="td_label">
                                    <xsl:text>Encounter Id</xsl:text>
                                 </span>
                              </td>
                              <td>
                                 <xsl:call-template name="show-id">
                                    <xsl:with-param name="id" select="n1:id"/>
                                 </xsl:call-template>
                              </td>
                           </xsl:otherwise>
                        </xsl:choose>
                     </tr>
                  </xsl:if>
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Encounter Date</xsl:text>
                        </span>
                     </td>
                     <td colspan="3">
                        <xsl:if test="n1:effectiveTime">
                           <xsl:choose>
                              <xsl:when test="n1:effectiveTime/@value">
                                 <xsl:text>&#160;at&#160;</xsl:text>
                                 <xsl:call-template name="show-time">
                                    <xsl:with-param name="datetime" select="n1:effectiveTime"/>
                                 </xsl:call-template>
                              </xsl:when>
                              <xsl:when test="n1:effectiveTime/n1:low">
                                 <xsl:text>&#160;From&#160;</xsl:text>
                                 <xsl:call-template name="show-time">
                                    <xsl:with-param name="datetime" select="n1:effectiveTime/n1:low"/>
                                 </xsl:call-template>
                                 <xsl:if test="n1:effectiveTime/n1:high">
                                    <xsl:text> to </xsl:text>
                                    <xsl:call-template name="show-time">
                                       <xsl:with-param name="datetime" select="n1:effectiveTime/n1:high"/>
                                    </xsl:call-template>
                                 </xsl:if>
                              </xsl:when>
                           </xsl:choose>
                        </xsl:if>
                     </td>
                  </tr>
                  <xsl:if test="n1:location/n1:healthCareFacility">
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Encounter Location</xsl:text>
                           </span>
                        </td>
                        <td colspan="3">
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
                                    <xsl:text>id: </xsl:text>
                                    <xsl:for-each select="n1:location/n1:healthCareFacility/n1:id">
                                       <xsl:call-template name="show-id">
                                          <xsl:with-param name="id" select="."/>
                                       </xsl:call-template>
                                    </xsl:for-each>
                                 </xsl:if>
                              </xsl:otherwise>
                           </xsl:choose>
                        </td>
                     </tr>
                  </xsl:if>
                  <xsl:if test="n1:responsibleParty">
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Responsible party</xsl:text>
                           </span>
                        </td>
                        <td colspan="3">
                           <xsl:call-template name="show-assignedEntity">
                              <xsl:with-param name="asgnEntity" select="n1:responsibleParty/n1:assignedEntity"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:if>
                  <xsl:if test="n1:responsibleParty/n1:assignedEntity/n1:addr | n1:responsibleParty/n1:assignedEntity/n1:telecom">
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Contact info</xsl:text>
                           </span>
                        </td>
                        <td colspan="3">
                           <xsl:call-template name="show-contactInfo">
                              <xsl:with-param name="contact" select="n1:responsibleParty/n1:assignedEntity"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:if>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- custodian -->
   <xsl:template name="custodian">
      <xsl:if test="n1:custodian">
         <table class="header_table">
            <tbody>
               <tr>
                  <td width="20%" bgcolor="#3399ff">
                     <span class="td_label">
                        <xsl:text>Document maintained by</xsl:text>
                     </span>
                  </td>
                  <td width="80%">
                     <xsl:choose>
                        <xsl:when test="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:name">
                           <xsl:call-template name="show-name">
                              <xsl:with-param name="name" select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:name"/>
                           </xsl:call-template>
                        </xsl:when>
                        <xsl:otherwise>
                           <xsl:for-each select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:id">
                              <xsl:call-template name="show-id"/>
                              <xsl:if test="position()!=last()">
                                 <br/>
                              </xsl:if>
                           </xsl:for-each>
                        </xsl:otherwise>
                     </xsl:choose>
                  </td>
               </tr>
               <xsl:if test="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:addr |             n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization/n1:telecom">
                  <tr>
                     <td bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Contact info</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
                        <xsl:call-template name="show-contactInfo">
                           <xsl:with-param name="contact" select="n1:custodian/n1:assignedCustodian/n1:representedCustodianOrganization"/>
                        </xsl:call-template>
                     </td>
                  </tr>
               </xsl:if>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- documentationOf -->
   <xsl:template name="documentationOf">
      <xsl:if test="n1:documentationOf">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:documentationOf">
                  <xsl:if test="n1:serviceEvent/@classCode and n1:serviceEvent/n1:code">
                     <xsl:variable name="displayName">
                        <xsl:call-template name="show-actClassCode">
                           <xsl:with-param name="clsCode" select="n1:serviceEvent/@classCode"/>
                        </xsl:call-template>
                     </xsl:variable>
                     <xsl:if test="$displayName">
                        <tr>
                           <td width="20%" bgcolor="#3399ff">
                              <span class="td_label">
                                 <xsl:call-template name="firstCharCaseUp">
                                    <xsl:with-param name="data" select="$displayName"/>
                                 </xsl:call-template>
                              </span>
                           </td>
                           <td width="80%" colspan="3">
                              <xsl:call-template name="show-code">
                                 <xsl:with-param name="code" select="n1:serviceEvent/n1:code"/>
                              </xsl:call-template>
                              <xsl:if test="n1:serviceEvent/n1:effectiveTime">
                                 <xsl:choose>
                                    <xsl:when test="n1:serviceEvent/n1:effectiveTime/@value">
                                       <xsl:text>&#160;at&#160;</xsl:text>
                                       <xsl:call-template name="show-time">
                                          <xsl:with-param name="datetime" select="n1:serviceEvent/n1:effectiveTime"/>
                                       </xsl:call-template>
                                    </xsl:when>
                                    <xsl:when test="n1:serviceEvent/n1:effectiveTime/n1:low">
                                       <xsl:text>&#160;from&#160;</xsl:text>
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
                           </td>
                        </tr>
                     </xsl:if>
                  </xsl:if>
                  <xsl:for-each select="n1:serviceEvent/n1:performer">
                     <xsl:variable name="displayName">
                        <xsl:call-template name="show-participationType">
                           <xsl:with-param name="ptype" select="@typeCode"/>
                        </xsl:call-template>
                        <xsl:text> </xsl:text>
                        <xsl:if test="n1:functionCode/@code">
                           <xsl:call-template name="show-participationFunction">
                              <xsl:with-param name="pFunction" select="n1:functionCode/@code"/>
                           </xsl:call-template>
                        </xsl:if>
                     </xsl:variable>
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:call-template name="firstCharCaseUp">
                                 <xsl:with-param name="data" select="$displayName"/>
                              </xsl:call-template>
                           </span>
                        </td>
                        <td width="80%" colspan="3">
                           <xsl:call-template name="show-assignedEntity">
                              <xsl:with-param name="asgnEntity" select="n1:assignedEntity"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:for-each>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- inFulfillmentOf -->
   <xsl:template name="inFulfillmentOf">
      <xsl:if test="n1:infulfillmentOf">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:inFulfillmentOf">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>In fulfillment of</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
                        <xsl:for-each select="n1:order">
                           <xsl:for-each select="n1:id">
                              <xsl:call-template name="show-id"/>
                           </xsl:for-each>
                           <xsl:for-each select="n1:code">
                              <xsl:text>&#160;</xsl:text>
                              <xsl:call-template name="show-code">
                                 <xsl:with-param name="code" select="."/>
                              </xsl:call-template>
                           </xsl:for-each>
                           <xsl:for-each select="n1:priorityCode">
                              <xsl:text>&#160;</xsl:text>
                              <xsl:call-template name="show-code">
                                 <xsl:with-param name="code" select="."/>
                              </xsl:call-template>
                           </xsl:for-each>
                        </xsl:for-each>
                     </td>
                  </tr>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- informant -->
   <xsl:template name="informant">
      <xsl:if test="n1:informant">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:informant">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Informant</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
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
                     </td>
                  </tr>
                  <xsl:choose>
                     <xsl:when test="n1:assignedEntity/n1:addr | n1:assignedEntity/n1:telecom">
                        <tr>
                           <td bgcolor="#3399ff">
                              <span class="td_label">
                                 <xsl:text>Contact info</xsl:text>
                              </span>
                           </td>
                           <td>
                              <xsl:if test="n1:assignedEntity">
                                 <xsl:call-template name="show-contactInfo">
                                    <xsl:with-param name="contact" select="n1:assignedEntity"/>
                                 </xsl:call-template>
                              </xsl:if>
                           </td>
                        </tr>
                     </xsl:when>
                     <xsl:when test="n1:relatedEntity/n1:addr | n1:relatedEntity/n1:telecom">
                        <tr>
                           <td bgcolor="#3399ff">
                              <span class="td_label">
                                 <xsl:text>Contact info</xsl:text>
                              </span>
                           </td>
                           <td>
                              <xsl:if test="n1:relatedEntity">
                                 <xsl:call-template name="show-contactInfo">
                                    <xsl:with-param name="contact" select="n1:relatedEntity"/>
                                 </xsl:call-template>
                              </xsl:if>
                           </td>
                        </tr>
                     </xsl:when>
                  </xsl:choose>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- informantionRecipient -->
   <xsl:template name="informationRecipient">
      <xsl:if test="n1:informationRecipient">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:informationRecipient">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Information recipient:</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
                        <xsl:choose>
                           <xsl:when test="n1:intendedRecipient/n1:informationRecipient/n1:name">
                              <xsl:for-each select="n1:intendedRecipient/n1:informationRecipient">
                                 <xsl:call-template name="show-name">
                                    <xsl:with-param name="name" select="n1:name"/>
                                 </xsl:call-template>
                                 <xsl:if test="position() != last()">
                                    <br/>
                                 </xsl:if>
                              </xsl:for-each>
                           </xsl:when>
                           <xsl:otherwise>
                              <xsl:for-each select="n1:intendedRecipient">
                                 <xsl:for-each select="n1:id">
                                    <xsl:call-template name="show-id"/>
                                 </xsl:for-each>
                                 <xsl:if test="position() != last()">
                                    <br/>
                                 </xsl:if>
                                 <br/>
                              </xsl:for-each>
                           </xsl:otherwise>
                        </xsl:choose>
                     </td>
                  </tr>
                  <xsl:if test="n1:intendedRecipient/n1:addr | n1:intendedRecipient/n1:telecom">
                     <tr>
                        <td bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Contact info</xsl:text>
                           </span>
                        </td>
                        <td>
                           <xsl:call-template name="show-contactInfo">
                              <xsl:with-param name="contact" select="n1:intendedRecipient"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:if>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- participant -->
   <xsl:template name="participant">
      <xsl:if test="n1:participant">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:participant">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <xsl:variable name="participtRole">
                           <xsl:call-template name="translateRoleAssoCode">
                              <xsl:with-param name="classCode" select="n1:associatedEntity/@classCode"/>
                              <xsl:with-param name="code" select="n1:associatedEntity/n1:code"/>
                           </xsl:call-template>
                        </xsl:variable>
                        <xsl:choose>
                           <xsl:when test="$participtRole">
                              <span class="td_label">
                                 <xsl:call-template name="firstCharCaseUp">
                                    <xsl:with-param name="data" select="$participtRole"/>
                                 </xsl:call-template>
                              </span>
                           </xsl:when>
                           <xsl:otherwise>
                              <span class="td_label">
                                 <xsl:text>Participant</xsl:text>
                              </span>
                           </xsl:otherwise>
                        </xsl:choose>
                     </td>
                     <td width="80%">
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
                     </td>
                  </tr>
                  <xsl:if test="n1:associatedEntity/n1:addr | n1:associatedEntity/n1:telecom">
                     <tr>
                        <td bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Contact info</xsl:text>
                           </span>
                        </td>
                        <td>
                           <xsl:call-template name="show-contactInfo">
                              <xsl:with-param name="contact" select="n1:associatedEntity"/>
                           </xsl:call-template>
                        </td>
                     </tr>
                  </xsl:if>
               </xsl:for-each>
            </tbody>
         </table>
      </xsl:if>
   </xsl:template>
   <!-- recordTarget -->
   <xsl:template name="recordTarget">
      <table class="header_table">
         <tbody>
            <xsl:for-each select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole">
               <xsl:if test="not(n1:id/@nullFlavor)">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Patient</xsl:text>
                        </span>
                     </td>
                     <td colspan="3">
                        <xsl:call-template name="show-name">
                           <xsl:with-param name="name" select="n1:patient/n1:name"/>
                        </xsl:call-template>
                     </td>
                  </tr>
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Date of birth</xsl:text>
                        </span>
                     </td>
                     <td width="30%">
                        <xsl:call-template name="show-time">
                           <xsl:with-param name="datetime" select="n1:patient/n1:birthTime"/>
                        </xsl:call-template>
                     </td>
                     <td width="15%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Sex</xsl:text>
                        </span>
                     </td>
                     <td>
                        <xsl:for-each select="n1:patient/n1:administrativeGenderCode">
                           <xsl:call-template name="show-gender"/>
                        </xsl:for-each>
                     </td>
                  </tr>
                  <xsl:if test="n1:patient/n1:raceCode | (n1:patient/n1:ethnicGroupCode)">
                     <tr>
                        <td width="20%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Race</xsl:text>
                           </span>
                        </td>
                        <td width="30%">
                           <xsl:choose>
                              <xsl:when test="n1:patient/n1:raceCode">
                                 <xsl:for-each select="n1:patient/n1:raceCode">
                                    <xsl:call-template name="show-race-ethnicity"/>
                                 </xsl:for-each>
                              </xsl:when>
                              <xsl:otherwise>
                                 <xsl:text>Information not available</xsl:text>
                              </xsl:otherwise>
                           </xsl:choose>
                        </td>
                        <td width="15%" bgcolor="#3399ff">
                           <span class="td_label">
                              <xsl:text>Ethnicity</xsl:text>
                           </span>
                        </td>
                        <td>
                           <xsl:choose>
                              <xsl:when test="n1:patient/n1:ethnicGroupCode">
                                 <xsl:for-each select="n1:patient/n1:ethnicGroupCode">
                                    <xsl:call-template name="show-race-ethnicity"/>
                                 </xsl:for-each>
                              </xsl:when>
                              <xsl:otherwise>
                                 <xsl:text>Information not available</xsl:text>
                              </xsl:otherwise>
                           </xsl:choose>
                        </td>
                     </tr>
                  </xsl:if>
                  <tr>
                     <td bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Contact info</xsl:text>
                        </span>
                     </td>
                     <td>
                        <xsl:call-template name="show-contactInfo">
                           <xsl:with-param name="contact" select="."/>
                        </xsl:call-template>
                     </td>
                     <td bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Patient IDs</xsl:text>
                        </span>
                     </td>
                     <td>
                        <xsl:for-each select="n1:id">
                           <xsl:call-template name="show-id"/>
                           <br/>
                        </xsl:for-each>
                     </td>
                  </tr>
               </xsl:if>
            </xsl:for-each>
         </tbody>
      </table>
   </xsl:template>
   <!-- relatedDocument -->
   <xsl:template name="relatedDocument">
      <xsl:if test="n1:relatedDocument">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:relatedDocument">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Related document</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
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
   <xsl:template name="authorization">
      <xsl:if test="n1:authorization">
         <table class="header_table">
            <tbody>
               <xsl:for-each select="n1:authorization">
                  <tr>
                     <td width="20%" bgcolor="#3399ff">
                        <span class="td_label">
                           <xsl:text>Consent</xsl:text>
                        </span>
                     </td>
                     <td width="80%">
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
   <xsl:template name="setAndVersion">
      <xsl:if test="n1:setId and n1:versionNumber">
         <table class="header_table">
            <tbody>
               <tr>
                  <td width="20%">
                     <xsl:text>SetId and Version</xsl:text>
                  </td>
                  <td colspan="3">
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
   <xsl:template match="n1:component/n1:structuredBody">
      <xsl:for-each select="n1:component/n1:section">
         <xsl:call-template name="section"/>
      </xsl:for-each>
   </xsl:template>
   <!-- show nonXMLBody -->
   <xsl:template match='n1:component/n1:nonXMLBody'>
      <xsl:choose>
         <!-- if there is a reference, use that in an IFRAME -->
         <xsl:when test='n1:text/n1:reference'>
            <IFRAME name='nonXMLBody' id='nonXMLBody' WIDTH='80%' HEIGHT='600' src='{n1:text/n1:reference/@value}'/>
         </xsl:when>
         <xsl:when test='n1:text/@mediaType="text/plain"'>
            <pre>
               <xsl:value-of select='n1:text/text()'/>
            </pre>
         </xsl:when>
         <xsl:otherwise>
            <CENTER>Cannot display the text</CENTER>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- top level component/section: display title and text,
     and process any nested component/sections
   -->
   <xsl:template name="section">
      <xsl:call-template name="section-title">
         <xsl:with-param name="title" select="n1:title"/>
      </xsl:call-template>
      <xsl:call-template name="section-author"/>
      <xsl:call-template name="section-text"/>
      <xsl:for-each select="n1:component/n1:section">
         <xsl:call-template name="nestedSection">
            <xsl:with-param name="margin" select="2"/>
         </xsl:call-template>
      </xsl:for-each>
   </xsl:template>
   <!-- top level section title -->
   <xsl:template name="section-title">
      <xsl:param name="title"/>
      <xsl:choose>
         <xsl:when test="count(/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component[n1:section]) &gt; 1">
            <h3>
               <a name="{generate-id($title)}" href="#toc">
                  <xsl:value-of select="$title"/>
               </a>
            </h3>
         </xsl:when>
         <xsl:otherwise>
            <h3>
               <xsl:value-of select="$title"/>
            </h3>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- section author -->
   <xsl:template name="section-author">
      <xsl:if test="count(n1:author)&gt;0">
         <div style="margin-left : 2em;">
            <b>
               <xsl:text>Section Author: </xsl:text>
            </b>
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
                     <xsl:value-of select="n1:assignedAuthoringDevice/n1:softwareName"/>
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
   <xsl:template name="section-text">
      <div>
         <xsl:apply-templates select="n1:text"/>
      </div>
   </xsl:template>
   <!-- nested component/section -->
   <xsl:template name="nestedSection">
      <xsl:param name="margin"/>
      <h4 style="margin-left : {$margin}em;">
         <xsl:value-of select="n1:title"/>
      </h4>
      <div style="margin-left : {$margin}em;">
         <xsl:apply-templates select="n1:text"/>
      </div>
      <xsl:for-each select="n1:component/n1:section">
         <xsl:call-template name="nestedSection">
            <xsl:with-param name="margin" select="2*$margin"/>
         </xsl:call-template>
      </xsl:for-each>
   </xsl:template>
   <!--   paragraph  -->
   <xsl:template match="n1:paragraph">
      <p>
         <xsl:apply-templates/>
      </p>
   </xsl:template>
   <!--   pre format  -->
   <xsl:template match="n1:pre">
      <pre>
         <xsl:apply-templates/>
      </pre>
   </xsl:template>
   <!--   Content w/ deleted text is hidden -->
   <xsl:template match="n1:content[@revised='delete']"/>
   <!--   content  -->
   <xsl:template match="n1:content">
      <xsl:apply-templates/>
   </xsl:template>
   <!-- line break -->
   <xsl:template match="n1:br">
      <xsl:element name='br'>
         <xsl:apply-templates/>
      </xsl:element>
   </xsl:template>
   <!--   list  -->
   <xsl:template match="n1:list">
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
   <xsl:template match="n1:list[@listType='ordered']">
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
   <xsl:template match="n1:caption">
      <xsl:apply-templates/>
      <xsl:text>: </xsl:text>
   </xsl:template>
   <!--  Tables   -->
   <xsl:template match="n1:table/@*|n1:thead/@*|n1:tfoot/@*|n1:tbody/@*|n1:colgroup/@*|n1:col/@*|n1:tr/@*|n1:th/@*|n1:td/@*">
      <xsl:copy>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </xsl:copy>
   </xsl:template>
   <xsl:template match="n1:table">
      <table class="narr_table">
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </table>
   </xsl:template>
   <xsl:template match="n1:thead">
      <thead>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </thead>
   </xsl:template>
   <xsl:template match="n1:tfoot">
      <tfoot>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </tfoot>
   </xsl:template>
   <xsl:template match="n1:tbody">
      <tbody>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </tbody>
   </xsl:template>
   <xsl:template match="n1:colgroup">
      <colgroup>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </colgroup>
   </xsl:template>
   <xsl:template match="n1:col">
      <col>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </col>
   </xsl:template>
   <xsl:template match="n1:tr">
      <tr class="narr_tr">
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </tr>
   </xsl:template>
   <xsl:template match="n1:th">
      <th class="narr_th">
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </th>
   </xsl:template>
   <xsl:template match="n1:td">
      <td>
         <xsl:copy-of select="@*"/>
         <xsl:apply-templates/>
      </td>
   </xsl:template>
   <xsl:template match="n1:table/n1:caption">
      <span style="font-weight:bold; ">
         <xsl:apply-templates/>
      </span>
   </xsl:template>
   <!--   RenderMultiMedia
    this currently only handles GIF's and JPEG's.  It could, however,
    be extended by including other image MIME types in the predicate
    and/or by generating <object> or <applet> tag with the correct
    params depending on the media type  @ID  =$imageRef  referencedObject
    -->
   <xsl:template match="n1:renderMultiMedia">
      <xsl:variable name="imageRef" select="@referencedObject"/>
      <xsl:choose>
         <xsl:when test="//n1:regionOfInterest[@ID=$imageRef]">
            <!-- Here is where the Region of Interest image referencing goes -->
            <xsl:if test="//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value[@mediaType='image/gif' or
 @mediaType='image/jpeg']">
               <br clear="all"/>
               <xsl:element name="img">
                  <xsl:attribute name="src"><xsl:value-of select="//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value/n1:reference/@value"/></xsl:attribute>
               </xsl:element>
            </xsl:if>
         </xsl:when>
         <xsl:otherwise>
            <!-- Here is where the direct MultiMedia image referencing goes -->
            <xsl:if test="//n1:observationMedia[@ID=$imageRef]/n1:value[@mediaType='image/gif' or @mediaType='image/jpeg']">
               <br clear="all"/>
               <xsl:element name="img">
                  <xsl:attribute name="src"><xsl:value-of select="//n1:observationMedia[@ID=$imageRef]/n1:value/n1:reference/@value"/></xsl:attribute>
               </xsl:element>
            </xsl:if>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!--    Stylecode processing
    Supports Bold, Underline and Italics display
    -->
   <xsl:template match="//n1:*[@styleCode]">
      <xsl:if test="@styleCode='Bold'">
         <xsl:element name="b">
            <xsl:apply-templates/>
         </xsl:element>
      </xsl:if>
      <xsl:if test="@styleCode='Italics'">
         <xsl:element name="i">
            <xsl:apply-templates/>
         </xsl:element>
      </xsl:if>
      <xsl:if test="@styleCode='Underline'">
         <xsl:element name="u">
            <xsl:apply-templates/>
         </xsl:element>
      </xsl:if>
      <xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Italics') and not (contains(@styleCode, 'Underline'))">
         <xsl:element name="b">
            <xsl:element name="i">
               <xsl:apply-templates/>
            </xsl:element>
         </xsl:element>
      </xsl:if>
      <xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Italics'))">
         <xsl:element name="b">
            <xsl:element name="u">
               <xsl:apply-templates/>
            </xsl:element>
         </xsl:element>
      </xsl:if>
      <xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Bold'))">
         <xsl:element name="i">
            <xsl:element name="u">
               <xsl:apply-templates/>
            </xsl:element>
         </xsl:element>
      </xsl:if>
      <xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and contains(@styleCode, 'Bold')">
         <xsl:element name="b">
            <xsl:element name="i">
               <xsl:element name="u">
                  <xsl:apply-templates/>
               </xsl:element>
            </xsl:element>
         </xsl:element>
      </xsl:if>
      <xsl:if test="not (contains(@styleCode,'Italics') or contains(@styleCode,'Underline') or contains(@styleCode, 'Bold'))">
         <xsl:apply-templates/>
      </xsl:if>
   </xsl:template>
   <!--    Superscript or Subscript   -->
   <xsl:template match="n1:sup">
      <xsl:element name="sup">
         <xsl:apply-templates/>
      </xsl:element>
   </xsl:template>
   <xsl:template match="n1:sub">
      <xsl:element name="sub">
         <xsl:apply-templates/>
      </xsl:element>
   </xsl:template>
   <!-- show-signature -->
   <xsl:template name="show-sig">
      <xsl:param name="sig"/>
      <xsl:choose>
         <xsl:when test="$sig/@code =&apos;S&apos;">
            <xsl:text>signed</xsl:text>
         </xsl:when>
         <xsl:when test="$sig/@code=&apos;I&apos;">
            <xsl:text>intended</xsl:text>
         </xsl:when>
         <xsl:when test="$sig/@code=&apos;X&apos;">
            <xsl:text>signature required</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <!--  show-id -->
   <xsl:template name="show-id">
      <xsl:param name="id"/>
      <xsl:choose>
         <xsl:when test="not($id)">
            <xsl:if test="not(@nullFlavor)">
               <xsl:if test="@extension">
                  <xsl:value-of select="@extension"/>
               </xsl:if>
               <xsl:text> </xsl:text>
               <xsl:value-of select="@root"/>
            </xsl:if>
         </xsl:when>
         <xsl:otherwise>
            <xsl:if test="not($id/@nullFlavor)">
               <xsl:if test="$id/@extension">
                  <xsl:value-of select="$id/@extension"/>
               </xsl:if>
               <xsl:text> </xsl:text>
               <xsl:value-of select="$id/@root"/>
            </xsl:if>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- show-name  -->
   <xsl:template name="show-name">
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
   <xsl:template name="show-gender">
      <xsl:choose>
         <xsl:when test="@code   = &apos;M&apos;">
            <xsl:text>Male</xsl:text>
         </xsl:when>
         <xsl:when test="@code  = &apos;F&apos;">
            <xsl:text>Female</xsl:text>
         </xsl:when>
         <xsl:when test="@code  = &apos;U&apos;">
            <xsl:text>Undifferentiated</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <!-- show-race-ethnicity  -->
   <xsl:template name="show-race-ethnicity">
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
   <xsl:template name="show-contactInfo">
      <xsl:param name="contact"/>
      <xsl:call-template name="show-address">
         <xsl:with-param name="address" select="$contact/n1:addr"/>
      </xsl:call-template>
      <xsl:call-template name="show-telecom">
         <xsl:with-param name="telecom" select="$contact/n1:telecom"/>
      </xsl:call-template>
   </xsl:template>
   <!-- show-address -->
   <xsl:template name="show-address">
      <xsl:param name="address"/>
      <xsl:choose>
         <xsl:when test="$address">
            <xsl:if test="$address/@use">
               <xsl:text> </xsl:text>
               <xsl:call-template name="translateTelecomCode">
                  <xsl:with-param name="code" select="$address/@use"/>
               </xsl:call-template>
               <xsl:text>:</xsl:text>
               <br/>
            </xsl:if>
            <xsl:for-each select="$address/n1:streetAddressLine">
               <xsl:value-of select="."/>
               <br/>
            </xsl:for-each>
            <xsl:if test="$address/n1:streetName">
               <xsl:value-of select="$address/n1:streetName"/>
               <xsl:text> </xsl:text>
               <xsl:value-of select="$address/n1:houseNumber"/>
               <br/>
            </xsl:if>
            <xsl:if test="string-length($address/n1:city)>0">
               <xsl:value-of select="$address/n1:city"/>
            </xsl:if>
            <xsl:if test="string-length($address/n1:state)>0">
               <xsl:text>,&#160;</xsl:text>
               <xsl:value-of select="$address/n1:state"/>
            </xsl:if>
            <xsl:if test="string-length($address/n1:postalCode)>0">
               <xsl:text>&#160;</xsl:text>
               <xsl:value-of select="$address/n1:postalCode"/>
            </xsl:if>
            <xsl:if test="string-length($address/n1:country)>0">
               <xsl:text>,&#160;</xsl:text>
               <xsl:value-of select="$address/n1:country"/>
            </xsl:if>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>address not available</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
      <br/>
   </xsl:template>
   <!-- show-telecom -->
   <xsl:template name="show-telecom">
      <xsl:param name="telecom"/>
      <xsl:choose>
         <xsl:when test="$telecom">
            <xsl:variable name="type" select="substring-before($telecom/@value, ':')"/>
            <xsl:variable name="value" select="substring-after($telecom/@value, ':')"/>
            <xsl:if test="$type">
               <xsl:call-template name="translateTelecomCode">
                  <xsl:with-param name="code" select="$type"/>
               </xsl:call-template>
               <xsl:if test="@use">
                  <xsl:text> (</xsl:text>
                  <xsl:call-template name="translateTelecomCode">
                     <xsl:with-param name="code" select="@use"/>
                  </xsl:call-template>
                  <xsl:text>)</xsl:text>
               </xsl:if>
               <xsl:text>: </xsl:text>
               <xsl:text> </xsl:text>
               <xsl:value-of select="$value"/>
            </xsl:if>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>Telecom information not available</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
      <br/>
   </xsl:template>
   <!-- show-recipientType -->
   <xsl:template name="show-recipientType">
      <xsl:param name="typeCode"/>
      <xsl:choose>
         <xsl:when test="$typeCode='PRCP'">Primary Recipient:</xsl:when>
         <xsl:when test="$typeCode='TRC'">Secondary Recipient:</xsl:when>
         <xsl:otherwise>Recipient:</xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- Convert Telecom URL to display text -->
   <xsl:template name="translateTelecomCode">
      <xsl:param name="code"/>
      <!--xsl:value-of select="document('voc.xml')/systems/system[@root=$code/@codeSystem]/code[@value=$code/@code]/@displayName"/-->
      <!--xsl:value-of select="document('codes.xml')/*/code[@code=$code]/@display"/-->
      <xsl:choose>
         <!-- lookup table Telecom URI -->
         <xsl:when test="$code='tel'">
            <xsl:text>Tel</xsl:text>
         </xsl:when>
         <xsl:when test="$code='fax'">
            <xsl:text>Fax</xsl:text>
         </xsl:when>
         <xsl:when test="$code='http'">
            <xsl:text>Web</xsl:text>
         </xsl:when>
         <xsl:when test="$code='mailto'">
            <xsl:text>Mail</xsl:text>
         </xsl:when>
         <xsl:when test="$code='H'">
            <xsl:text>Home</xsl:text>
         </xsl:when>
         <xsl:when test="$code='HV'">
            <xsl:text>Vacation Home</xsl:text>
         </xsl:when>
         <xsl:when test="$code='HP'">
            <xsl:text>Pirmary Home</xsl:text>
         </xsl:when>
         <xsl:when test="$code='WP'">
            <xsl:text>Work Place</xsl:text>
         </xsl:when>
         <xsl:when test="$code='PUB'">
            <xsl:text>Pub</xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>{$code='</xsl:text>
            <xsl:value-of select="$code"/>
            <xsl:text>'?}</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- convert RoleClassAssociative code to display text -->
   <xsl:template name="translateRoleAssoCode">
      <xsl:param name="classCode"/>
      <xsl:param name="code"/>
      <xsl:choose>
         <xsl:when test="$classCode='AFFL'">
            <xsl:text>affiliate</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='AGNT'">
            <xsl:text>agent</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='ASSIGNED'">
            <xsl:text>assigned entity</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='COMPAR'">
            <xsl:text>commissioning party</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='CON'">
            <xsl:text>contact</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='ECON'">
            <xsl:text>emergency contact</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='NOK'">
            <xsl:text>next of kin</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='SGNOFF'">
            <xsl:text>signing authority</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='GUARD'">
            <xsl:text>guardian</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='GUAR'">
            <xsl:text>guardian</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='CIT'">
            <xsl:text>citizen</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='COVPTY'">
            <xsl:text>covered party</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='PRS'">
            <xsl:text>personal relationship</xsl:text>
         </xsl:when>
         <xsl:when test="$classCode='CAREGIVER'">
            <xsl:text>care giver</xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>{$classCode='</xsl:text>
            <xsl:value-of select="$classCode"/>
            <xsl:text>'?}</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="($code/@code) and ($code/@codeSystem='2.16.840.1.113883.5.111')">
         <xsl:text> </xsl:text>
         <xsl:choose>
            <xsl:when test="$code/@code='FTH'">
               <xsl:text>(Father)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='MTH'">
               <xsl:text>(Mother)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='NPRN'">
               <xsl:text>(Natural parent)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='STPPRN'">
               <xsl:text>(Step parent)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='SONC'">
               <xsl:text>(Son)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='DAUC'">
               <xsl:text>(Daughter)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='CHILD'">
               <xsl:text>(Child)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='EXT'">
               <xsl:text>(Extended family member)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='NBOR'">
               <xsl:text>(Neighbor)</xsl:text>
            </xsl:when>
            <xsl:when test="$code/@code='SIGOTHR'">
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
   <xsl:template name="show-time">
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
   <xsl:template name="facilityAndDates">
      <table class="header_table">
         <tbody>
            <!-- facility id -->
            <tr>
               <td width="20%" bgcolor="#3399ff">
                  <span class="td_label">
                     <xsl:text>Facility ID</xsl:text>
                  </span>
               </td>
               <td colspan="3">
                  <xsl:choose>
                     <xsl:when test="count(/n1:ClinicalDocument/n1:participant
                                      [@typeCode='LOC'][@contextControlCode='OP']
                                      /n1:associatedEntity[@classCode='SDLOC']/n1:id)&gt;0">
                        <!-- change context node -->
                        <xsl:for-each select="/n1:ClinicalDocument/n1:participant
                                      [@typeCode='LOC'][@contextControlCode='OP']
                                      /n1:associatedEntity[@classCode='SDLOC']/n1:id">
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
                     <xsl:otherwise>
                 Not available
                             </xsl:otherwise>
                  </xsl:choose>
               </td>
            </tr>
            <!-- Period reported -->
            <tr>
               <td width="20%" bgcolor="#3399ff">
                  <span class="td_label">
                     <xsl:text>First day of period reported</xsl:text>
                  </span>
               </td>
               <td colspan="3">
                  <xsl:call-template name="show-time">
                     <xsl:with-param name="datetime" select="/n1:ClinicalDocument/n1:documentationOf
                                      /n1:serviceEvent/n1:effectiveTime/n1:low"/>
                  </xsl:call-template>
               </td>
            </tr>
            <tr>
               <td width="20%" bgcolor="#3399ff">
                  <span class="td_label">
                     <xsl:text>Last day of period reported</xsl:text>
                  </span>
               </td>
               <td colspan="3">
                  <xsl:call-template name="show-time">
                     <xsl:with-param name="datetime" select="/n1:ClinicalDocument/n1:documentationOf
                                      /n1:serviceEvent/n1:effectiveTime/n1:high"/>
                  </xsl:call-template>
               </td>
            </tr>
         </tbody>
      </table>
   </xsl:template>
   <!-- show assignedEntity -->
   <xsl:template name="show-assignedEntity">
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
                  <xsl:when test="position()!=last()">
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
   <xsl:template name="show-relatedEntity">
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
   <xsl:template name="show-associatedEntity">
      <xsl:param name="assoEntity"/>
      <xsl:choose>
         <xsl:when test="$assoEntity/n1:associatedPerson">
            <xsl:for-each select="$assoEntity/n1:associatedPerson/n1:name">
               <xsl:call-template name="show-name">
                  <xsl:with-param name="name" select="."/>
               </xsl:call-template>
               <br/>
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
   <xsl:template name="show-code">
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
         <!--
      <xsl:when test="$the-valuesets/*/voc:system[@root=$this-codeSystem]/voc:code[@value=$this-code]/@displayName">
        <xsl:value-of select="$the-valuesets/*/voc:system[@root=$this-codeSystem]/voc:code[@value=$this-code]/@displayName"/>
      </xsl:when>
      -->
         <xsl:otherwise>
            <xsl:value-of select="$this-code"/>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>
   <!-- show classCode -->
   <xsl:template name="show-actClassCode">
      <xsl:param name="clsCode"/>
      <xsl:choose>
         <xsl:when test=" $clsCode = 'ACT' ">
            <xsl:text>healthcare service</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'ACCM' ">
            <xsl:text>accommodation</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'ACCT' ">
            <xsl:text>account</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'ACSN' ">
            <xsl:text>accession</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'ADJUD' ">
            <xsl:text>financial adjudication</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'CONS' ">
            <xsl:text>consent</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'CONTREG' ">
            <xsl:text>container registration</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'CTTEVENT' ">
            <xsl:text>clinical trial timepoint event</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'DISPACT' ">
            <xsl:text>disciplinary action</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'ENC' ">
            <xsl:text>encounter</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'INC' ">
            <xsl:text>incident</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'INFRM' ">
            <xsl:text>inform</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'INVE' ">
            <xsl:text>invoice element</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'LIST' ">
            <xsl:text>working list</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'MPROT' ">
            <xsl:text>monitoring program</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'PCPR' ">
            <xsl:text>care provision</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'PROC' ">
            <xsl:text>procedure</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'REG' ">
            <xsl:text>registration</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'REV' ">
            <xsl:text>review</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'SBADM' ">
            <xsl:text>substance administration</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'SPCTRT' ">
            <xsl:text>speciment treatment</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'SUBST' ">
            <xsl:text>substitution</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'TRNS' ">
            <xsl:text>transportation</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'VERIF' ">
            <xsl:text>verification</xsl:text>
         </xsl:when>
         <xsl:when test=" $clsCode = 'XACT' ">
            <xsl:text>financial transaction</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <!-- show participationType -->
   <xsl:template name="show-participationType">
      <xsl:param name="ptype"/>
      <xsl:choose>
         <xsl:when test=" $ptype='PPRF' ">
            <xsl:text>primary performer</xsl:text>
         </xsl:when>
         <xsl:when test=" $ptype='PRF' ">
            <xsl:text>performer</xsl:text>
         </xsl:when>
         <xsl:when test=" $ptype='VRF' ">
            <xsl:text>verifier</xsl:text>
         </xsl:when>
         <xsl:when test=" $ptype='SPRF' ">
            <xsl:text>secondary performer</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <!-- show participationFunction -->
   <xsl:template name="show-participationFunction">
      <xsl:param name="pFunction"/>
      <xsl:choose>
         <!-- From the HL7 v3 ParticipationFunction code system -->
         <xsl:when test=" $pFunction = 'ADMPHYS' ">
            <xsl:text>(admitting physician)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'ANEST' ">
            <xsl:text>(anesthesist)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'ANRS' ">
            <xsl:text>(anesthesia nurse)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'ATTPHYS' ">
            <xsl:text>(attending physician)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'DISPHYS' ">
            <xsl:text>(discharging physician)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'FASST' ">
            <xsl:text>(first assistant surgeon)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'MDWF' ">
            <xsl:text>(midwife)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'NASST' ">
            <xsl:text>(nurse assistant)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'PCP' ">
            <xsl:text>(primary care physician)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'PRISURG' ">
            <xsl:text>(primary surgeon)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'RNDPHYS' ">
            <xsl:text>(rounding physician)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'SASST' ">
            <xsl:text>(second assistant surgeon)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'SNRS' ">
            <xsl:text>(scrub nurse)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'TASST' ">
            <xsl:text>(third assistant)</xsl:text>
         </xsl:when>
         <!-- From the HL7 v2 Provider Role code system (2.16.840.1.113883.12.443) which is used by HITSP -->
         <xsl:when test=" $pFunction = 'CP' ">
            <xsl:text>(consulting provider)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'PP' ">
            <xsl:text>(primary care provider)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'RP' ">
            <xsl:text>(referring provider)</xsl:text>
         </xsl:when>
         <xsl:when test=" $pFunction = 'MP' ">
            <xsl:text>(medical home provider)</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <xsl:template name="formatDateTime">
      <xsl:param name="date"/>
      <!-- month -->
      <xsl:variable name="month" select="substring ($date, 5, 2)"/>
      <xsl:choose>
         <xsl:when test="$month='01'">
            <xsl:text>January </xsl:text>
         </xsl:when>
         <xsl:when test="$month='02'">
            <xsl:text>February </xsl:text>
         </xsl:when>
         <xsl:when test="$month='03'">
            <xsl:text>March </xsl:text>
         </xsl:when>
         <xsl:when test="$month='04'">
            <xsl:text>April </xsl:text>
         </xsl:when>
         <xsl:when test="$month='05'">
            <xsl:text>May </xsl:text>
         </xsl:when>
         <xsl:when test="$month='06'">
            <xsl:text>June </xsl:text>
         </xsl:when>
         <xsl:when test="$month='07'">
            <xsl:text>July </xsl:text>
         </xsl:when>
         <xsl:when test="$month='08'">
            <xsl:text>August </xsl:text>
         </xsl:when>
         <xsl:when test="$month='09'">
            <xsl:text>September </xsl:text>
         </xsl:when>
         <xsl:when test="$month='10'">
            <xsl:text>October </xsl:text>
         </xsl:when>
         <xsl:when test="$month='11'">
            <xsl:text>November </xsl:text>
         </xsl:when>
         <xsl:when test="$month='12'">
            <xsl:text>December </xsl:text>
         </xsl:when>
      </xsl:choose>
      <!-- day -->
      <xsl:choose>
         <xsl:when test='substring ($date, 7, 1)="0"'>
            <xsl:value-of select="substring ($date, 8, 1)"/>
            <xsl:text>, </xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="substring ($date, 7, 2)"/>
            <xsl:text>, </xsl:text>
         </xsl:otherwise>
      </xsl:choose>
      <!-- year -->
      <xsl:value-of select="substring ($date, 1, 4)"/>
      <!-- time and US timezone -->
      <xsl:if test="string-length($date) > 8">
         <xsl:text>, </xsl:text>
         <!-- time -->
         <xsl:variable name="time">
            <xsl:value-of select="substring($date,9,6)"/>
         </xsl:variable>
         <xsl:variable name="hh">
            <xsl:value-of select="substring($time,1,2)"/>
         </xsl:variable>
         <xsl:variable name="mm">
            <xsl:value-of select="substring($time,3,2)"/>
         </xsl:variable>
         <xsl:variable name="ss">
            <xsl:value-of select="substring($time,5,2)"/>
         </xsl:variable>
         <xsl:if test="string-length($hh)&gt;1">
            <xsl:value-of select="$hh"/>
            <xsl:if test="string-length($mm)&gt;1 and not(contains($mm,'-')) and not (contains($mm,'+'))">
               <xsl:text>:</xsl:text>
               <xsl:value-of select="$mm"/>
               <xsl:if test="string-length($ss)&gt;1 and not(contains($ss,'-')) and not (contains($ss,'+'))">
                  <xsl:text>:</xsl:text>
                  <xsl:value-of select="$ss"/>
               </xsl:if>
            </xsl:if>
         </xsl:if>
         <!-- time zone -->
         <xsl:variable name="tzon">
            <xsl:choose>
               <xsl:when test="contains($date,'+')">
                  <xsl:text>+</xsl:text>
                  <xsl:value-of select="substring-after($date, '+')"/>
               </xsl:when>
               <xsl:when test="contains($date,'-')">
                  <xsl:text>-</xsl:text>
                  <xsl:value-of select="substring-after($date, '-')"/>
               </xsl:when>
            </xsl:choose>
         </xsl:variable>
         <xsl:choose>
            <!-- reference: http://www.timeanddate.com/library/abbreviations/timezones/na/ -->
            <xsl:when test="$tzon = '-0500' ">
               <xsl:text>, EST</xsl:text>
            </xsl:when>
            <xsl:when test="$tzon = '-0600' ">
               <xsl:text>, CST</xsl:text>
            </xsl:when>
            <xsl:when test="$tzon = '-0700' ">
               <xsl:text>, MST</xsl:text>
            </xsl:when>
            <xsl:when test="$tzon = '-0800' ">
               <xsl:text>, PST</xsl:text>
            </xsl:when>
            <xsl:otherwise>
               <xsl:text> </xsl:text>
               <xsl:value-of select="$tzon"/>
            </xsl:otherwise>
         </xsl:choose>
      </xsl:if>
   </xsl:template>
   <!-- convert to lower case -->
   <xsl:template name="caseDown">
      <xsl:param name="data"/>
      <xsl:if test="$data">
         <xsl:value-of select="translate($data, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')"/>
      </xsl:if>
   </xsl:template>
   <!-- convert to upper case -->
   <xsl:template name="caseUp">
      <xsl:param name="data"/>
      <xsl:if test="$data">
         <xsl:value-of select="translate($data,'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
      </xsl:if>
   </xsl:template>
   <!-- convert first character to upper case -->
   <xsl:template name="firstCharCaseUp">
      <xsl:param name="data"/>
      <xsl:if test="$data">
         <xsl:call-template name="caseUp">
            <xsl:with-param name="data" select="substring($data,1,1)"/>
         </xsl:call-template>
         <xsl:value-of select="substring($data,2)"/>
      </xsl:if>
   </xsl:template>
   <!-- show-noneFlavor -->
   <xsl:template name="show-noneFlavor">
      <xsl:param name="nf"/>
      <xsl:choose>
         <xsl:when test=" $nf = 'NI' ">
            <xsl:text>no information</xsl:text>
         </xsl:when>
         <xsl:when test=" $nf = 'INV' ">
            <xsl:text>invalid</xsl:text>
         </xsl:when>
         <xsl:when test=" $nf = 'MSK' ">
            <xsl:text>masked</xsl:text>
         </xsl:when>
         <xsl:when test=" $nf = 'NA' ">
            <xsl:text>not applicable</xsl:text>
         </xsl:when>
         <xsl:when test=" $nf = 'UNK' ">
            <xsl:text>unknown</xsl:text>
         </xsl:when>
         <xsl:when test=" $nf = 'OTH' ">
            <xsl:text>other</xsl:text>
         </xsl:when>
      </xsl:choose>
   </xsl:template>
   <xsl:template name="addCSS">
      <style>
         <xsl:text>
body {
  color: #003366;
  background-color: #FFFFFF;
  font-family: Verdana, Tahoma, sans-serif;
  font-size: 11px;
}

a {
  color: #003366;
  background-color: #FFFFFF;
}

h1 {
  font-size: 12pt;
  font-weight: bold;
}

h2 {
  font-size: 11pt;
  font-weight: bold;
}

h3 {
  font-size: 10pt;
  font-weight: bold;
}

h4 {
  font-size: 8pt;
  font-weight: bold;
}

div {
  width: 80%;
}

table {
  line-height: 10pt;
  width: 80%;
}

tr {
  background-color: #ccccff;
}

td {
  padding: 0.1cm 0.2cm;
  vertical-align: top;
}

.h1center {
  font-size: 12pt;
  font-weight: bold;
  text-align: center;
  width: 80%;
}

.header_table{
  border: 1pt inset #00008b;
}

.narr_table {
  width: 100%;
}

.narr_tr {
  background-color: #ffffcc;
}

.narr_th {
  background-color: #ffd700;
}

.td_label{
  font-weight: bold;
  color: white;
}
          </xsl:text>
      </style>
   </xsl:template>
</xsl:stylesheet>
