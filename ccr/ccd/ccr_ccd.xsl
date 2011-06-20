<?xml version="1.0" encoding="utf-8"?>
<!--
Conversion of CCR to Level 3 CCD

Orginal Author:   	Ken Miller
Solventus LLC
ken.miller@solventus.coms

Contributors:
Richard Braman, EHR Doctors, Inc rbraman@ehrdoctors.com
George Lilly (WorldVistA glilly@glilly.net)
xxxx - Oroville Hospital

Date: 	2010-05-5
Version: 	0.1

License :

        This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program.  If not, see http://www.gnu.org/licenses.

-->
<xsl:stylesheet version="1.0" xmlns="urn:hl7-org:v3" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:a="urn:astm-org:CCR" xmlns:date="http://exslt.org/dates-and-times"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" exclude-result-prefixes="a date">
    <xsl:import href="./templates/cdavocablookup.xsl"/>
    <xsl:import href="./templates/hl7oidlookup.xsl"/>
    <xsl:import href="./templates/code.xsl"/>
    <xsl:import href="./templates/actor.xsl"/>
    <xsl:import href="./templates/datetime.xsl"/>
    <xsl:import href="./templates/problemDescription.xsl"/>

    <xsl:output method="xml" encoding="utf-8" version="1.0" indent="yes"/>
    <xsl:template match="/">
        <ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:voc="urn:hl7-org:v3/voc" xmlns:sdtc="urn:hl7-org:sdtc"  xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd" classCode="DOCCLIN" moodCode="EVN">
            <realmCode code="US"/>
            <typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
            <templateId root="2.16.840.1.113883.10.20.1"/>
            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.1.1"/>
            <templateId root="2.16.840.1.113883.10.20.3"/>
            <templateId root="2.16.840.1.113883.3.88.11.32.1"/>

            <id>
                <xsl:attribute name="root">
                    <xsl:value-of select="/a:ContinuityOfCareRecord/a:CCRDocumentObjectID"></xsl:value-of>
                </xsl:attribute>
            </id>
            <code code="34133-9" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Summarization of episode note"/>
             <xsl:variable name="fromID" select="/a:ContinuityOfCareRecord/a:From/a:ActorLink[1]/a:ActorID"/>
              <xsl:variable name="ccrFromActorObjectID" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$fromID]/a:InternalCCRLink[a:LinkRelationship='representedOrganization']/a:LinkID"/>
            <title>Continuity of Care Document from <xsl:value-of select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrFromActorObjectID]/a:Organization/a:Name"/></title>
            <effectiveTime>
                <xsl:attribute name="value">
                    <xsl:call-template name="date:format-date">
                        <xsl:with-param name="date-time" select="/a:ContinuityOfCareRecord/a:DateTime/a:ExactDateTime"/>
                        <xsl:with-param name="pattern">yyyyMMddhhmmss+0000</xsl:with-param>
                    </xsl:call-template>
                </xsl:attribute>
            </effectiveTime>
            <confidentialityCode code="N" codeSystem="2.16.840.1.113883.5.25"/>
            <languageCode code="en-US"/>

            <recordTarget typeCode="RCT" contextControlCode="OP">
            <patientRole>
                <xsl:call-template name="ccdPatientRole">
                    <xsl:with-param name="ccrActorObjectID" select="/a:ContinuityOfCareRecord/a:Patient[1]/a:ActorID"/>
                </xsl:call-template>
                </patientRole>
            </recordTarget>

            <author>
                <time>
                    <xsl:attribute name="value">
                        <xsl:call-template name="date:format-date">
                            <xsl:with-param name="date-time" select="/a:ContinuityOfCareRecord/a:DateTime/a:ExactDateTime"/>
                            <xsl:with-param name="pattern">yyyyMMddhhmmss</xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                </time>
                <xsl:call-template name="ccdAssignedAuthor">
                    <xsl:with-param name="ccrActorObjectID" select="$fromID"/>
                </xsl:call-template>
            </author>

            <xsl:if test="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$fromID]/a:InternalCCRLink[a:LinkRelationship='representedOrganization']">
                <custodian>
                    <assignedCustodian>
                        <xsl:call-template name="ccdOrganization">
                            <xsl:with-param name="ccrActorObjectID" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$fromID]/a:InternalCCRLink[a:LinkRelationship='representedOrganization']/a:LinkID"/>
                            <xsl:with-param name="organizationNodeName" select="'representedCustodianOrganization'"/>
                        </xsl:call-template>
                    </assignedCustodian>
                </custodian>
            </xsl:if> 

            <documentationOf>
                <serviceEvent classCode="PCPR">
                    <effectiveTime>
                        <low>
                            <xsl:attribute name="value">
                                <xsl:call-template name="date:format-date">
                                    <xsl:with-param name="date-time">
                                        <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body//a:DateTime//a:ExactDateTime">
                                            <xsl:sort order="ascending"/>
                                            <xsl:if test="position()=1">
                                                <xsl:value-of select="."/>
                                            </xsl:if>
                                        </xsl:for-each>
                                    </xsl:with-param>
                                    <xsl:with-param name="pattern">yyyyMMdd</xsl:with-param>
                                </xsl:call-template>
                            </xsl:attribute>
                        </low>
                        <high>
                            <xsl:attribute name="value">
                                <xsl:call-template name="date:format-date">
                                    <xsl:with-param name="date-time">
                                        <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body//a:DateTime//a:ExactDateTime">
                                            <xsl:sort order="descending"/>
                                            <xsl:if test="position()=1">
                                                <xsl:value-of select="."/>
                                            </xsl:if>
                                        </xsl:for-each>
                                    </xsl:with-param>
                                    <xsl:with-param name="pattern">yyyyMMdd</xsl:with-param>
                                </xsl:call-template>
                            </xsl:attribute>
                        </high>
                    </effectiveTime>
                </serviceEvent>

                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:HealthCareProviders/a:Provider">
                    <xsl:call-template name="ccdAssignedEntity">
                        <xsl:with-param name="ccrActorObjectID" select="a:ActorID"/>
                    </xsl:call-template>
                </xsl:for-each>

            </documentationOf>

            <component>
                <structuredBody>
                    <component>
                        <section>
                            <templateId root="2.16.840.1.113883.10.20.1.13"/>
                            <code code="48764-5" codeSystem="2.16.840.1.113883.6.1" />
                            <title>Purpose</title>
                            <text>
                                <xsl:value-of select="/a:ContinuityOfCareRecord/a:Purpose/a:Description/a:Text"></xsl:value-of>
                            </text>
                        </section>
                    </component>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Alerts">
                        <component>
                            <section>
							  <templateId root="2.16.840.1.113883.10.20.1.2" />
							  <!--C83 Allergies and Other Adverse Reactions Section Conformance Identifier-->
							  <templateId root="2.16.840.1.113883.3.88.11.83.102" />
							  <!--IHE Allergies and Other Adverse Reactions Section Conformance Identifier-->
							  <templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.13" />
                                <code code="48765-2" displayName="Allergies, adverse reactions, alerts" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" />
                                <title>Alerts</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Reaction</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Alerts/a:Alert">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:apply-templates select="a:Description/a:Code"/>
                                                    </td>
                                                    <td>
                                                        <xsl:attribute name="ID">
															<xsl:value-of select="a:CCRDataObjectID"/>
														</xsl:attribute>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Reaction/a:Description/a:Text"/>
                                                        <xsl:if test="a:Reaction/a:Severity/a:Text">
                                                            -<xsl:value-of select="a:Reaction/a:Severity/a:Text"/>
                                                        </xsl:if>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Alerts/a:Alert">
                                    <entry typeCode="DRIV">
                                        <act classCode="ACT" moodCode="EVN">
                                             <!--CCD Problem Act Identifier-->
											 <templateId root="2.16.840.1.113883.10.20.1.27"></templateId>
											 <!--C83 Allergy Entry-->
											 <templateId root="2.16.840.1.113883.3.88.11.83.6" />
											 <!--IHE Concern Entry Conformance Identifier-->
											 <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1"></templateId>
											 <!--IHE Allergy and Intolerance Concerns Entry-->
											 <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.3"></templateId>

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <code nullFlavor="NA"/>
                                            <entryRelationship typeCode="SUBJ">
                                                <observation classCode="OBS" moodCode="EVN">
                                                      <!--CCD Alert Observation-->
													  <templateId root="2.16.840.1.113883.10.20.1.18"></templateId>
													  <!--CCD Problem Observation-->
													  <templateId root="2.16.840.1.113883.10.20.1.28" />
													  <!--IHE Problem Entry-->
													  <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" />
													  <!--IHE Allergies and Intolerances Entry-->
													  <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.6" />
                                                    <!-- <id> -->
                                                    <xsl:call-template name="ccdID">
                                                        <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                                        <xsl:with-param name="suffix"></xsl:with-param>
                                                    </xsl:call-template>

                                                    <code code="416098002" codeSystem="2.16.840.1.113883.6.96" displayName="drug allergy" codeSystemName="SNOMED CT"/>

                                                    <text>
                                                        <reference>
                                                            <xsl:attribute name="value">
                                                                <xsl:text>#</xsl:text>
                                                                <xsl:value-of select="a:CCRDataObjectID"/>
                                                            </xsl:attribute>
                                                        </reference>
                                                    </text>

                                                    <statusCode code="completed"/>
													<value xsi:type="CD" />
													
                                                   <participant typeCode="CSM">
                                                        <xsl:choose>
                                                            <xsl:when test="a:Agent/a:Products/a:Product/a:Product">
                                                                <xsl:call-template name="ccdParticipantRoleCodedDescription">
                                                                    <xsl:with-param name="ccrCodedDescription" select="a:Product/a:Description"/>
                                                                </xsl:call-template>
                                                            </xsl:when>
                                                            
                                                        </xsl:choose>
                                                    </participant>

                                                    <xsl:if test="a:Reaction">
                                                        <entryRelationship typeCode="MFST" inversionInd="true">
                                                            <observation classCode="OBS" moodCode="EVN">
                                                                <templateId root="2.16.840.1.113883.10.20.1.54"/>
                                                                <!--Reaction observation template -->
                                                                <code code="ASSERTION" codeSystem="2.16.840.1.113883.5.4"/>
                                                                <statusCode code="completed"/>
                                                                <xsl:call-template name="ccdCodedValue">
                                                                    <xsl:with-param name="ccrCodedDescription" select="a:Reaction/a:Description"/>
                                                                </xsl:call-template>
                                                            </observation>
                                                        </entryRelationship>
                                                    </xsl:if>

                                                    <xsl:call-template name="ccdStatus">
                                                        <xsl:with-param name="ccrStatus" select="a:Status"/>
                                                    </xsl:call-template>

                                                </observation>
                                            </entryRelationship>
                                        </act>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:AdvanceDirectives">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.1" assigningAuthorityName="HL7 CCD"/>
                                <templateId root="2.16.840.1.113883.3.88.11.83.116" assigningAuthorityName="HITSP/C83"/>
                                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.35" assigningAuthorityName="IHE PCC"/>
                                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.34" assigningAuthorityName="IHE PCC"/>
                                <!--Advance Directives section template-->
                                <code code="42348-3" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Advance directives"/>
                                <title>Advance Directives</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:AdvanceDirectives/a:AdvanceDirective">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:attribute name="id">
                                                            <xsl:value-of select="a:CCRDataObjectID"/>
                                                            <xsl:text>:Narrative</xsl:text>
                                                        </xsl:attribute>

                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:AdvanceDirectives/a:AdvanceDirective">
                                    <entry typeCode="DRIV">
                                        <observation classCode="OBS" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.3.88.11.83.12" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.17" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13.7" assigningAuthorityName="IHE PCC"/>
                                            <!-- Advance directive observation template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <statusCode code="completed"/>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                <xsl:with-param name="originalTextReference">
                                                    <xsl:text>#</xsl:text>
                                                    <xsl:value-of select="a:CCRDataObjectID"/>
                                                    <xsl:text>:Narrative</xsl:text>
                                                </xsl:with-param>
                                            </xsl:call-template>

                                            <participant typeCode="VRF">
                                                <templateId root="2.16.840.1.113883.10.20.1.58"/>
                                                <!--Verification of an advance directive observation template -->
                                                <time nullFlavor="UNK"/>
                                                <xsl:call-template name="ccdParticipantRoleActor">
                                                    <xsl:with-param name="ccrActorObjectID" select="a:Source[1]/a:ActorID"/>
                                                </xsl:call-template>
                                            </participant>

                                            <xsl:if test="a:ReferenceID">
                                                <reference typeCode="REFR">
                                                    <externalDocument>
                                                        <templateId root="2.16.840.1.113883.10.20.1.36"/>
                                                        <!-- Advance directive reference template -->
                                                        <xsl:variable name="referenceID" select="a:ReferenceID"/>
                                                        <!-- <id> -->
                                                        <xsl:call-template name="ccdID">
                                                            <xsl:with-param name="ccrObjectID" select="$referenceID"/>
                                                        </xsl:call-template>
                                                        <code code="371538006" codeSystem="2.16.840.1.113883.6.96" displayName="Advance directive"/>
                                                        <xsl:variable name="reference" select="/a:ContinuityOfCareRecord/a:References/a:Reference[a:ReferenceObjectID=$referenceID]"/>
                                                        <text>
                                                            <xsl:attribute name="mediaType">
                                                                <xsl:value-of select="$reference/a:Type/a:Text"/>
                                                            </xsl:attribute>
                                                            <reference>
                                                                <xsl:value-of select="$reference/a:Location[1]/a:Description/a:Text"/>
                                                            </reference>
                                                        </text>
                                                    </externalDocument>
                                                </reference>
                                            </xsl:if>
                                        </observation>
                                    </entry>

                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:FunctionalStatus">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.5"/>
                                <code code="47420-5" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Functional Status</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <xsl:value-of select="a:Type/a:Text"/>
                                                </td>
                                                <td>
                                                    <xsl:call-template name="date:format-date">
                                                        <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                    </xsl:call-template>
                                                </td>
                                                <td>
                                                    <xsl:apply-templates select="a:Description/a:Code"/>
                                                </td>
                                                <td>
                                                    <xsl:value-of select="a:Description/a:Text"/>
                                                </td>
                                                <td>
                                                    <xsl:value-of select="a:Status/a:Text"/>
                                                </td>
                                                <td>
                                                    <xsl:call-template name="actorName">
                                                        <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                    </xsl:call-template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </text>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Problems">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.3.88.11.83.103" assigningAuthorityName="HITSP/C83"/>
                                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.6" assigningAuthorityName="IHE PCC"/>
                                <templateId root="2.16.840.1.113883.10.20.1.11" assigningAuthorityName="HL7 CCD"/>
                                <code code="11450-4" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Problem list"/>
                                <title>Problems</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Problems/a:Problem">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <table>
                                                            <tbody>
                                                                <xsl:apply-templates select="a:DateTime"/>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td>
                                                        <xsl:apply-templates select="a:Description/a:Code"/>
                                                    </td>
                                                    <td>
														<xsl:attribute name="ID">
															<xsl:value-of select="a:CCRDataObjectID"></xsl:value-of>
														</xsl:attribute>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Problems/a:Problem">
                                    <entry typeCode="DRIV">
                                        <act classCode="ACT" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.10.20.1.27"/>
                                            <!-- Problem act template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <code nullFlavor="NA"/>

                                            <xsl:call-template name="ccdPerformer">
                                                <xsl:with-param name="ccrActorReference" select="a:Source/a:Actor"/>
                                            </xsl:call-template>

                                            <entryRelationship typeCode="SUBJ">
                                                <observation classCode="OBS" moodCode="EVN">
                                                    <templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
                                                    <!--Problem observation template-->

                                                    <!-- <id> -->
                                                    <xsl:call-template name="ccdID">
                                                        <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                                        <xsl:with-param name="suffix"></xsl:with-param>
                                                    </xsl:call-template>

                                                    <code code="55607006" displayName="Problem" codeSystem="2.16.840.1.113883.6.96" codeSystemName="SNOMED-CT"/>

                                                    <text>
                                                        <reference>
                                                            <xsl:attribute name="value">
                                                                <xsl:text>#</xsl:text>
                                                                <xsl:value-of select="a:CCRDataObjectID"/>
                                                            </xsl:attribute>
                                                        </reference>
                                                    </text>

                                                    <statusCode code="completed"/>

                                                    <xsl:call-template name="ccdDateTime">
                                                        <xsl:with-param name="dt" select="a:DateTime"/>
                                                    </xsl:call-template>

                                                    <xsl:call-template name="ccdCodedValue">
                                                        <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                    </xsl:call-template>

                                                    <xsl:call-template name="ccdStatus">
                                                        <xsl:with-param name="ccrStatus" select="a:Status"/>
                                                    </xsl:call-template>
                                                </observation>
                                            </entryRelationship>

                                        </act>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Procedures">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.12"/>
                                <code code="47519-4" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Procedures</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Location</th>
                                                <th>Substance</th>
                                                <th>Method</th>
                                                <th>Position</th>
                                                <th>Site</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Procedures/a:Procedure">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>

                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <table>
                                                        <tbody>
                                                            <xsl:apply-templates select="a:DateTime"/>
                                                        </tbody>
                                                    </table>
                                                    <td>
                                                        <xsl:apply-templates select="a:Description/a:Code"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Locations/a:Location">
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                            <xsl:if test="a:Actor">
                                                                (<xsl:call-template name="actorName">
                                                                    <xsl:with-param name="objID" select="a:Actor/a:ActorID"/>
                                                                </xsl:call-template>
                                                                <xsl:if test="a:Actor/a:ActorRole/a:Text">
                                                                    <xsl:text xml:space="preserve"> - </xsl:text><xsl:value-of select="a:ActorRole/a:Text"/>)
                                                                </xsl:if>
                                                            </xsl:if>)
                                                            <xsl:if test="position() != last()">
                                                                <br/>
                                                            </xsl:if>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Substance">
                                                            <xsl:value-of select="a:Text"/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Method/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Position/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Site/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>

                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Procedures/a:Procedure">
                                    <entry typeCode="DRIV">
                                        <procedure classCode="PROC" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.3.88.11.83.17" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.29" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.19" assigningAuthorityName="IHE PCC"/>

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <!-- <code> -->
                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                <xsl:with-param name="nodeName" select="'code'"/>
                                            </xsl:call-template>

                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <xsl:call-template name="ccdStatusProcedure">
                                                <xsl:with-param name="status" select="a:Status"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Method">
                                                <xsl:call-template name="ccdCodedValue">
                                                    <xsl:with-param name="ccrCodedDescription" select="a:Method"/>
                                                    <xsl:with-param name="nodeName" select="'approachSiteCode'"/>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <xsl:if test="a:Site">
                                                <xsl:call-template name="ccdCodedValue">
                                                    <xsl:with-param name="ccrCodedDescription" select="a:Site"/>
                                                    <xsl:with-param name="nodeName" select="'targetSiteCode'"/>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <xsl:if test="a:Practitioners/a:Practitioner">
                                                <xsl:call-template name="ccdPerformer">
                                                    <xsl:with-param name="ccrActorReference" select="a:Practitioners/a:Practitioner[1]"/>
                                                </xsl:call-template>
                                            </xsl:if>
                                        </procedure>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Medications">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.3.88.11.83.112" assigningAuthorityName="HITSP/C83"/>
                                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.3.19" assigningAuthorityName="IHE PCC"/>
                                <templateId root="2.16.840.1.113883.10.20.1.8" assigningAuthorityName="HL7 CCD"/>
                                <!--Medications section template-->
                                <code code="10160-0" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="History of medication use"/>
                                <title>Medications</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Medication</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Form</th>
                                                <th>Strength</th>
                                                <th>Quantity</th>
                                                <th>SIG</th>
                                                <th>Indications</th>
                                                <th>Instruction</th>
                                                <th>Refills</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Medications/a:Medication">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Product/a:ProductName/a:Text"/>
                                                        <xsl:if test="a:Product/a:BrandName">
                                                            <xsl:text xml:space="preserve"> </xsl:text>(<xsl:value-of select="a:Product/a:BrandName/a:Text"/>)
                                                        </xsl:if>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Product/a:Form/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Product/a:Strength">
                                                            <xsl:if test="position() > 1">
                                                                <xsl:text>/</xsl:text>
                                                            </xsl:if>
                                                            <xsl:value-of select="a:Value"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:Units/a:Unit"/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Quantity/a:Value"/>
                                                        <xsl:text xml:space="preserve"> </xsl:text>
                                                        <xsl:value-of select="a:Quantity/a:Units/a:Unit"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Directions/a:Direction">
                                                            <xsl:choose>
                                                                <xsl:when test="a:Description/a:Text">
                                                                    <xsl:value-of select="a:Description/a:Text"/>
                                                                </xsl:when>
                                                                <xsl:otherwise>
                                                                    <xsl:value-of select="a:Dose/a:Value"/>
                                                                    <xsl:text xml:space="preserve"> </xsl:text>
                                                                    <xsl:value-of select="a:Dose/a:Units/a:Unit"/>
                                                                    <xsl:text xml:space="preserve"> </xsl:text>
                                                                    <xsl:value-of select="a:Route/a:Text"/>
                                                                    <xsl:text xml:space="preserve"> </xsl:text>
                                                                    <xsl:value-of select="a:Frequency/a:Value"/>
                                                                    <xsl:if test="a:Duration">
                                                                        <xsl:text xml:space="preserve">( </xsl:text>for <xsl:value-of select="a:Duration/a:Value"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Duration/a:Units/a:Unit"/><xsl:text xml:space="preserve"> )</xsl:text>
                                                                    </xsl:if>
                                                                    <xsl:if test="a:MultipleDirectionModifier/a:ObjectAttribute">
                                                                        <xsl:for-each select="a:MultipleDirectionModifier/a:ObjectAttribute">
                                                                            <xsl:value-of select="a:Attribute"/>
                                                                            <br/>
                                                                            <xsl:value-of select="a:AttributeValue/a:Value"/>
                                                                        </xsl:for-each>
                                                                    </xsl:if>
                                                                </xsl:otherwise>
                                                            </xsl:choose>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Directions/a:Direction[1]/a:Indication">
                                                            <xsl:call-template name="problemDescription">
                                                                <xsl:with-param name="objID" select="a:InternalCCRLink/a:LinkID"/>
                                                            </xsl:call-template>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:PatientInstructions/a:Instruction">
                                                            <xsl:value-of select="a:Text"/>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Refills/a:Refill">
                                                            <xsl:value-of select="a:Number"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Medications/a:Medication">
                                    <entry typeCode="DRIV">
                                        <substanceAdministration classCode="SBADM" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>

                                            <!--Medication activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <statusCode code='completed'/>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                                <xsl:with-param name="type" select="'IVL_TS'"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdMedicationFrequency">
                                                <xsl:with-param name="frequency" select="a:Directions/a:Direction/a:Frequency"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Route"/>
                                                <xsl:with-param name="nodeName" select="'routeCode'"/>
                                                <xsl:with-param name="domain" select="'RouteOfAdministration'"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Directions/a:Direction/a:Dose">
                                                <doseQuantity>
                                                    <low>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Value"></xsl:value-of>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="unit">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Unit"></xsl:value-of>
                                                        </xsl:attribute>
                                                    </low>
                                                    <high>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Value"></xsl:value-of>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="unit">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Unit"></xsl:value-of>
                                                        </xsl:attribute>
                                                    </high>
                                                </doseQuantity>
                                            </xsl:if>
                                            <consumable>
                                                <manufacturedProduct>
                                                    <templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
                                                    <templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
                                                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>

                                                    <!-- Product template -->

                                                    <manufacturedMaterial>
                                                        <xsl:call-template name="ccdCodedValue">
                                                            <xsl:with-param name="ccrCodedDescription" select="a:Product/a:ProductName"/>
                                                            <xsl:with-param name="nodeName" select="'code'"/>
                                                        </xsl:call-template>
                                                        <name>
                                                            <xsl:value-of select="a:Product/a:BrandName/a:Text"/>
                                                        </name>
                                                    </manufacturedMaterial>
                                                </manufacturedProduct>
                                            </consumable>
                                        </substanceAdministration>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Immunizations">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.6"/>
                                <code code="11369-6" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Immunizations</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Code</th>
                                                <th>Vaccine</th>
                                                <th>Date</th>
                                                <th>Route</th>
                                                <th>Site</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Immunizations/a:Immunization">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                    <td>
                                                        <xsl:apply-templates select="a:Product/a:ProductName/a:Code"/>
                                                    </td>
                                                    <td>

                                                        <xsl:value-of select="a:Product/a:ProductName/a:Text"/>
                                                        <xsl:if test="a:Product/a:Form">
                                                            <xsl:text xml:space="preserve"> </xsl:text>(<xsl:value-of select="a:Product/a:Form/a:Text"/>)
                                                        </xsl:if>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Directions/a:Direction/a:Route/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:attribute name="id">
                                                            <xsl:value-of select="a:CCRDataObjectID"/>
                                                            <xsl:text>:Site</xsl:text>
                                                        </xsl:attribute>

                                                        <xsl:value-of select="a:Directions/a:Direction/a:Site/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Immunizations/a:Immunization">
                                    <entry typeCode="DRIV">
                                        <substanceAdministration classCode="SBADM" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
                                            <templateId root="2.16.840.1.113883.3.88.11.83.13" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.12" assigningAuthorityName="IHE PCC"/>

                                            <!-- Medication activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <code code="IMMUNIZ" codeSystem="2.16.840.1.113883.5.4" codeSystemName="HL7 ActCode"/>
                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <statusCode code='completed'/>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                                <xsl:with-param name="type" select="'IVL_TS'"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Route"/>
                                                <xsl:with-param name="nodeName" select="'routeCode'"/>
                                                <xsl:with-param name="domain" select="'RouteOfAdministration'"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Directions/a:Direction/a:Site">
                                                <xsl:call-template name="ccdCodedValue">
                                                    <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Site"/>
                                                    <xsl:with-param name="nodeName" select="'approachSiteCode'"/>
                                                    <xsl:with-param name="originalTextReference">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                        <xsl:text>:Site</xsl:text>
                                                    </xsl:with-param>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <consumable>
                                                <manufacturedProduct>
                                                    <templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
                                                    <templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
                                                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>

                                                    <!-- Product template -->

                                                    <manufacturedMaterial>
                                                        <xsl:call-template name="ccdCodedValue">
                                                            <xsl:with-param name="ccrCodedDescription" select="a:Product/a:ProductName"/>
                                                            <xsl:with-param name="nodeName" select="'code'"/>
                                                        </xsl:call-template>
                                                        <name>
                                                            <xsl:value-of select="a:Product/a:BrandName/a:Text"/>
                                                        </name>
                                                    </manufacturedMaterial>
                                                </manufacturedProduct>
                                            </consumable>

                                        </substanceAdministration>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:VitalSigns">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.16"/>
                                <code code="8716-3" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Vital Signs</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Vital Sign</th>
                                                <th>Date</th>
                                                <th>Result</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:VitalSigns/a:Result">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>

                                                    <td>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Test">
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:TestResult/a:Value"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:Flag/a:Text"/>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:VitalSigns/a:Result">
                                    <entry typeCode="DRIV">
                                        <organizer classCode="CLUSTER" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.10.20.1.32" assigningAuthorityName="CCD"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.35" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13.1" assigningAuthorityName="IHE PCC"/>
                                            <!-- Vital signs organizer template -->

                                            <xsl:variable name="testDate" select="a:DateTime"/>

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <code code="46680005" codeSystem="2.16.840.1.113883.6.96" displayName="Vital signs" codeSystemName="SNOMED CT"/>
                                            <statusCode code="completed"/>
                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="$testDate"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdObservation">
                                                <xsl:with-param name="ccrTestNode" select="a:Test[1]"/>
                                                <xsl:with-param name="testDate" select="$testDate"/>
                                            </xsl:call-template>

                                        </organizer>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Encounters">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.3.88.11.83.127" assigningAuthorityName="HITSP/C83"/>
                                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.1.5.3.3" assigningAuthorityName="IHE PCC"/>
                                <templateId root="2.16.840.1.113883.10.20.1.3" assigningAuthorityName="HL7 CCD"/>
                                <!--Encounters section template-->
                                <code code="46240-8" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="History of encounters"/>
                                <title>Encounters</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Practitioner</th>
                                                <th>Description</th>
                                                <th>Indications</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Encounters/a:Encounter">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>

                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Locations/a:Location">
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                            <xsl:call-template name="actorName">
                                                                <xsl:with-param name="objID" select="a:Actor/a:ActorID"/>
                                                            </xsl:call-template>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Practitioners/a:Practitioner">
                                                            <xsl:call-template name="actorName">
                                                                <xsl:with-param name="objID" select="a:ActorID"/>
                                                            </xsl:call-template>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Indications/a:Indication">
                                                            <xsl:call-template name="problemDescription">
                                                                <xsl:with-param name="objID" select="a:InternalCCRLink/a:LinkID"/>
                                                            </xsl:call-template>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Encounters/a:Encounter">
                                    <entry typeCode="DRIV">
                                        <encounter classCode="ENC" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.3.88.11.83.16" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.21" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.14" assigningAuthorityName="IHE PCC"/>

                                            <!-- Encounter activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                <xsl:with-param name="nodeName" select="'code'"/>
                                            </xsl:call-template>

                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Practitioners[1]/a:Practitioner">
                                                <xsl:call-template name="ccdPerformer">
                                                    <xsl:with-param name="ccrActorReference" select="a:Practitioners/a:Practitioner[1]"/>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <xsl:if test="a:Locations[1]/a:Location">
                                                <participant typeCode="LOC">
                                                    <templateId root="2.16.840.1.113883.10.20.1.45"/>
                                                    <!-- Location participation template -->
                                                    <xsl:choose>
                                                        <xsl:when test="a:Locations[1]/a:Location/a:ActorID">
                                                            <xsl:call-template name="ccdParticipantRoleActor">
                                                                <xsl:with-param name="ccrActorObjectID" select="a:Locations[1]/a:Location/a:ActorID"/>
                                                            </xsl:call-template>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <xsl:call-template name="ccdParticipantRoleCodedDescription">
                                                                <xsl:with-param name="ccrCodedDescription" select="a:Locations[1]/a:Location/a:Description"/>
                                                            </xsl:call-template>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </participant>
                                            </xsl:if>
                                        </encounter>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:SocialHistory">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.15"/>
                                <code code="29762-2" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Social History</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:apply-templates select="a:Description/a:Code"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Description/a:Text" disable-output-escaping="yes"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement">
                                    <entry typeCode="DRIV">
                                        <observation classCode="OBS" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.3.88.11.83.19" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.33" assigningAuthorityName="CCD"/>
                                            <!-- Social history observation template -->
                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>
                                            <statusCode code="completed"/>
                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                            </xsl:call-template>
                                            <entryRelationship typeCode="SUBJ" inversionInd="true">
                                                <observation classCode="OBS" moodCode="EVN">
                                                    <templateId root="2.16.840.1.113883.10.20.1.41"/>
                                                    <!-- Episode observation template -->
                                                    <statusCode code="completed"/>
                                                    <entryRelationship typeCode="SAS">
                                                        <observation classCode="OBS" moodCode="EVN">
                                                            <xsl:call-template name="ccdCodedValue">
                                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                                <xsl:with-param name="nodeName" select="'code'"/>
                                                            </xsl:call-template>
                                                        </observation>
                                                    </entryRelationship>
                                                </observation>
                                            </entryRelationship>
                                        </observation>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:FamilyHistory">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.4"/>
                                <code code="10157-6" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Family History</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Relationship(s)</th>
                                                <th>Status</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:FamilyHistory/a:FamilyProblemHistory">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:apply-templates select="a:Problem/a:Description/a:Code"/>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Problem">
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                            <br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:FamilyMember/a:ActorRole/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Status/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Results/a:Result">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.14"/>
                                <code code="30954-2" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Results</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Test</th>
                                                <th>Date</th>
                                                <th>Result</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Results/a:Result">
                                                <tr>
                                                    <xsl:attribute name="id">
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                    <td>
                                                        <xsl:value-of select="a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:Test[a:TestResult/a:Value!='']">
                                                            <div>
                                                                <xsl:attribute name="id">
                                                                    <xsl:value-of select="a:CCRDataObjectID"/>
                                                                </xsl:attribute>

                                                                <xsl:value-of select="a:Description/a:Text"/>
                                                                <xsl:text xml:space="preserve"> </xsl:text>
                                                                <xsl:value-of select="a:TestResult/a:Value"/>
                                                                <xsl:text xml:space="preserve"> </xsl:text>
                                                                <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                                <xsl:text xml:space="preserve"> </xsl:text>
                                                                <xsl:value-of select="a:Flag/a:Text"/>
                                                                <br/>
                                                            </div>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Results/a:Result">
                                    <entry typeCode="DRIV">
                                        <xsl:variable name="testDate" select="a:DateTime"/>

                                        <xsl:choose>
                                            <xsl:when test="count(a:Test)>1">
                                                <organizer classCode="BATTERY" moodCode="EVN">
                                                    <templateId root="2.16.840.1.113883.10.20.1.32"/>
                                                    <!--Result organizer template -->

                                                    <!-- <id> -->
                                                    <xsl:call-template name="ccdID">
                                                        <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                                    </xsl:call-template>

                                                    <!-- <code> -->
                                                    <xsl:call-template name="ccdCodedValue">
                                                        <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                        <xsl:with-param name="nodeName" select="'code'"/>
                                                    </xsl:call-template>

                                                    <statusCode code="completed"/>

                                                    <!-- <effectiveTime> -->
                                                    <xsl:call-template name="ccdDateTime">
                                                        <xsl:with-param name="dt" select="$testDate"/>
                                                    </xsl:call-template>

                                                    <xsl:call-template name="ccdPerformer">
                                                        <xsl:with-param name="ccrActorReference" select="a:Source/a:Actor"/>
                                                    </xsl:call-template>

                                                    <xsl:for-each select="a:Test">
                                                        <xsl:call-template name="ccdObservation">
                                                            <xsl:with-param name="ccrTestNode" select="."/>
                                                            <xsl:with-param name="testDate" select="$testDate"/>
                                                        </xsl:call-template>
                                                    </xsl:for-each>

                                                </organizer>

                                            </xsl:when>
                                            <xsl:otherwise>
                                                <xsl:call-template name="ccdObservation">
                                                    <xsl:with-param name="ccrTestNode" select="a:Test[1]"/>
                                                    <xsl:with-param name="testDate" select="$testDate"/>
                                                </xsl:call-template>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Payers">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.9"/>
                                <code code="48768-6" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Insurance</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Identification Numbers</th>
                                                <th>Payment Provider</th>
                                                <th>Subscriber</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Payers/a:Payer">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:for-each select="a:IDs">
                                                            <xsl:value-of select="a:Type/a:Text"/>:<xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:ID"/><br/>
                                                        </xsl:for-each>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:PaymentProvider/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Subscriber/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Payers/a:Payer">
                                    <entry typeCode="DRIV">
                                        <act classCode="ACT" moodCode="DEF">
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.17" assigningAuthorityName="IHE PCC"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.20" assigningAuthorityName="CCD"/>
                                            <!-- Coverage entry template -->
                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>
                                            <code code="48768-6" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Payment sources"/>
                                            <statusCode code="completed"/>
                                            <entryRelationship typeCode="COMP">
                                                <act classCode="ACT" moodCode="EVN">
                                                    <templateId root="2.16.840.1.113883.3.88.11.83.5" assigningAuthorityName="HITSP C83"/>
                                                    <templateId root="2.16.840.1.113883.10.20.1.26" assigningAuthorityName="CCD"/>
                                                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.18" assigningAuthorityName="IHE PCC"/>
                                                    <!--Insurance provider template -->
                                                    <id>
                                                        <xsl:attribute name="root">
                                                            <xsl:value-of select="a:IDs[1]/a:ID"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="extension">
                                                            <xsl:value-of select="a:IDs[1]/a:Type/a:Text"/>
                                                        </xsl:attribute>
                                                    </id>

                                                    <!-- <code> -->
                                                    <xsl:call-template name="ccdCodedValue">
                                                        <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                        <xsl:with-param name="nodeName" select="'code'"/>
                                                    </xsl:call-template>

                                                    <statusCode code="completed"/>

                                                    <xsl:call-template name="ccdPerformer">
                                                        <xsl:with-param name="ccrActorReference" select="a:PaymentProvider"/>
                                                    </xsl:call-template>

                                                    <participant typeCode="HLD">
                                                        <xsl:call-template name="ccdParticipantRoleActor">
                                                            <xsl:with-param name="ccrActorReference" select="a:Subscriber"/>
                                                        </xsl:call-template>
                                                    </participant>
                                                </act>
                                            </entryRelationship>
                                        </act>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare">
                        <component>
                            <section>
                                <templateId root="2.16.840.1.113883.10.20.1.10"/>
                                <code code="18776-5" codeSystem="2.16.840.1.113883.6.1"/>
                                <title>Plan Of Care</title>
                                <text>
                                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Treatment Recommendation']">
                                        <xsl:text>Plan Of Care Recommendations</xsl:text>
                                        <br/>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Recommendation</th>
                                                    <th>Goal</th>
                                                    <th>Status</th>
                                                    <th>Source</th>
                                                </tr>
                                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Treatment Recommendation']">
                                                    <tr>
                                                        <td>
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Description/a:Text" disable-output-escaping="yes"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Goals/a:Goal/a:Description/a:Text" disable-output-escaping="yes"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Status/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:call-template name="actorName">
                                                                <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                            </xsl:call-template>
                                                        </td>
                                                    </tr>
                                                </xsl:for-each>
                                            </tbody>
                                        </table>
                                    </xsl:if>
                                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Order']">
                                        <xsl:text>Plan Of Care Orders</xsl:text>
                                        <br/>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <th>Descripion</th>
                                                    <th>Plan Status</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Procedure</th>
                                                    <th>Schedule</th>
                                                    <th>Location</th>
                                                    <th>Substance</th>
                                                    <th>Method</th>
                                                    <th>Position</th>
                                                    <th>Site</th>
                                                    <th>Status</th>
                                                    <th>Source</th>
                                                </tr>
                                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Order']">
                                                    <tr>
                                                        <td>
                                                            <xsl:apply-templates select="a:Description/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Status/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Type/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:call-template name="date:format-date">
                                                                <xsl:with-param name="date-time" select="a:OrderRequest/a:Procedures/a:Procedure/a:DateTime/a:ExactDateTime"/>
                                                            </xsl:call-template>
                                                        </td>
                                                        <td>
                                                            <xsl:apply-templates select="a:OrderRequest/a:Procedures/a:Procedure/a:Description/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:text xml:space="preserve">Every </xsl:text>
                                                            <xsl:apply-templates select="a:OrderRequest/a:Procedures/a:Procedure/a:Interval/a:Value"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Interval/a:Units/a:Unit"/>
                                                            <xsl:text xml:space="preserve"> for </xsl:text>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Duration/a:Value"/>
                                                            <xsl:text xml:space="preserve"> </xsl:text>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Duration/a:Units/a:Unit"/>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:OrderRequest/a:Procedures/a:Procedure/a:Locations">
                                                                <xsl:value-of select="a:Location/a:Description/a:Text"/>
                                                                <xsl:if test="position() != last()">
                                                                    <br/>
                                                                </xsl:if>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Substance/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Method/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Position/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Site/a:Text"/>
                                                        </td>
                                                        <td/>
                                                        <td>
                                                            <xsl:call-template name="actorName">
                                                                <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                            </xsl:call-template>
                                                        </td>
                                                    </tr>
                                                </xsl:for-each>
                                            </tbody>
                                        </table>
                                    </xsl:if>
                                </text>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan/a:OrderRequest/a:Procedures/a:Procedure">
                                    <entry typeCode="DRIV">
                                        <observation classCode="OBS" moodCode="RQO">
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.1.20.3.1"/>

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <!-- <code> -->
                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                <xsl:with-param name="nodeName" select="'code'"/>
                                            </xsl:call-template>

                                            <statusCode code="new"/>

                                            <effectiveTime>
                                                <xsl:choose>
                                                    <xsl:when test="a:DateTime">
                                                        <xsl:call-template name="ccdDateTime">
                                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                                        </xsl:call-template>
                                                    </xsl:when>
                                                    <xsl:when test="a:Interval">
                                                        <xsl:attribute name="xsi:type">PIVL_TS</xsl:attribute>
                                                        <xsl:attribute name="operator">A</xsl:attribute>
                                                        <xsl:attribute name="institutionSpecified">true</xsl:attribute>
                                                        <period>
                                                            <xsl:attribute name="value">
                                                                <xsl:value-of select="a:Interval/a:Value"/>
                                                            </xsl:attribute>
                                                            <xsl:attribute name="unit">
                                                                <xsl:value-of select="a:Interval/a:Units/a:Unit"/>
                                                            </xsl:attribute>
                                                        </period>
                                                    </xsl:when>
                                                </xsl:choose>
                                            </effectiveTime>
                                        </observation>
                                    </entry>
                                </xsl:for-each>

                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan/a:OrderRequest/a:Medications/a:Medication">
                                    <entry typeCode="DRIV">
                                        <substanceAdministration classCode="SBADM" moodCode="INT">
                                            <templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>

                                            <!--Medication activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <statusCode code='completed'/>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                                <xsl:with-param name="type" select="'IVL_TS'"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdMedicationFrequency">
                                                <xsl:with-param name="frequency" select="a:Directions/a:Direction/a:Frequency"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Route"/>
                                                <xsl:with-param name="nodeName" select="'routeCode'"/>
                                                <xsl:with-param name="domain" select="'RouteOfAdministration'"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Directions/a:Direction/a:Dose">
                                                <doseQuantity>
                                                    <low>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Value"></xsl:value-of>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="unit">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Unit"></xsl:value-of>
                                                        </xsl:attribute>
                                                    </low>
                                                    <high>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Value"></xsl:value-of>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="unit">
                                                            <xsl:value-of select="a:Directions/a:Direction/a:Dose/a:Unit"></xsl:value-of>
                                                        </xsl:attribute>
                                                    </high>
                                                </doseQuantity>
                                            </xsl:if>
                                            <consumable>
                                                <manufacturedProduct>
                                                    <templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
                                                    <templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
                                                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>

                                                    <!-- Product template -->

                                                    <manufacturedMaterial>
                                                        <xsl:call-template name="ccdCodedValue">
                                                            <xsl:with-param name="ccrCodedDescription" select="a:Product/a:ProductName"/>
                                                            <xsl:with-param name="nodeName" select="'code'"/>
                                                        </xsl:call-template>
                                                        <name>
                                                            <xsl:value-of select="a:Product/a:BrandName/a:Text"/>
                                                        </name>
                                                    </manufacturedMaterial>
                                                </manufacturedProduct>
                                            </consumable>
                                        </substanceAdministration>
                                    </entry>
                                </xsl:for-each>
                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan/a:OrderRequest/a:Immunizations/a:Immunization">

                                    <entry typeCode="DRIV">
                                        <substanceAdministration classCode="SBADM" moodCode="EVN">
                                            <templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
                                            <templateId root="2.16.840.1.113883.3.88.11.83.13" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.12" assigningAuthorityName="IHE PCC"/>

                                            <!-- Medication activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <code code="IMMUNIZ" codeSystem="2.16.840.1.113883.5.4" codeSystemName="HL7 ActCode"/>
                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <statusCode code='completed'/>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                                <xsl:with-param name="type" select="'IVL_TS'"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Route"/>
                                                <xsl:with-param name="nodeName" select="'routeCode'"/>
                                                <xsl:with-param name="domain" select="'RouteOfAdministration'"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Directions/a:Direction/a:Site">
                                                <xsl:call-template name="ccdCodedValue">
                                                    <xsl:with-param name="ccrCodedDescription" select="a:Directions/a:Direction/a:Site"/>
                                                    <xsl:with-param name="nodeName" select="'approachSiteCode'"/>
                                                    <xsl:with-param name="originalTextReference">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                        <xsl:text>:Site</xsl:text>
                                                    </xsl:with-param>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <consumable>
                                                <manufacturedProduct>
                                                    <templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
                                                    <templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
                                                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>

                                                    <!-- Product template -->

                                                    <manufacturedMaterial>
                                                        <xsl:call-template name="ccdCodedValue">
                                                            <xsl:with-param name="ccrCodedDescription" select="a:Product/a:ProductName"/>
                                                            <xsl:with-param name="nodeName" select="'code'"/>
                                                        </xsl:call-template>
                                                        <name>
                                                            <xsl:value-of select="a:Product/a:BrandName/a:Text"/>
                                                        </name>
                                                    </manufacturedMaterial>
                                                </manufacturedProduct>
                                            </consumable>

                                        </substanceAdministration>
                                    </entry>
                                </xsl:for-each>

                                <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan/a:OrderRequest/a:Encounters/a:Encounter">
                                    <entry typeCode="DRIV">
                                        <encounter classCode="ENC" moodCode="INT">
                                            <templateId root="2.16.840.1.113883.3.88.11.83.16" assigningAuthorityName="HITSP C83"/>
                                            <templateId root="2.16.840.1.113883.10.20.1.21" assigningAuthorityName="CCD"/>
                                            <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.14" assigningAuthorityName="IHE PCC"/>

                                            <!-- Encounter activity template -->

                                            <!-- <id> -->
                                            <xsl:call-template name="ccdID">
                                                <xsl:with-param name="ccrObjectID" select="a:CCRDataObjectID"/>
                                            </xsl:call-template>

                                            <xsl:call-template name="ccdCodedValue">
                                                <xsl:with-param name="ccrCodedDescription" select="a:Description"/>
                                                <xsl:with-param name="nodeName" select="'code'"/>
                                            </xsl:call-template>

                                            <text>
                                                <reference>
                                                    <xsl:attribute name="value">
                                                        <xsl:text>#</xsl:text>
                                                        <xsl:value-of select="a:CCRDataObjectID"/>
                                                    </xsl:attribute>
                                                </reference>
                                            </text>

                                            <xsl:call-template name="ccdDateTime">
                                                <xsl:with-param name="dt" select="a:DateTime"/>
                                            </xsl:call-template>

                                            <xsl:if test="a:Practitioners[1]/a:Practitioner">
                                                <xsl:call-template name="ccdPerformer">
                                                    <xsl:with-param name="ccrActorReference" select="a:Practitioners/a:Practitioner[1]"/>
                                                </xsl:call-template>
                                            </xsl:if>

                                            <xsl:if test="a:Locations[1]/a:Location">
                                                <participant typeCode="LOC">
                                                    <templateId root="2.16.840.1.113883.10.20.1.45"/>
                                                    <!-- Location participation template -->
                                                    <xsl:choose>
                                                        <xsl:when test="a:Locations[1]/a:Location/a:ActorID">
                                                            <xsl:call-template name="ccdParticipantRoleActor">
                                                                <xsl:with-param name="ccrActorObjectID" select="a:Locations[1]/a:Location/a:ActorID"/>
                                                            </xsl:call-template>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <xsl:call-template name="ccdParticipantRoleCodedDescription">
                                                                <xsl:with-param name="ccrCodedDescription" select="a:Locations[1]/a:Location/a:Description"/>
                                                            </xsl:call-template>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </participant>
                                            </xsl:if>
                                        </encounter>
                                    </entry>
                                </xsl:for-each>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:HealthCareProviders">
                        <component>
                            <section>
                                <title>Health Care Providers</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Role</th>
                                                <th>Name</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:HealthCareProviders/a:Provider">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:ActorRole/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                            </section>
                        </component>
                    </xsl:if>
                    <!--
                    <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:SupportProviders">
                        <component>
                            <section>
                                <title>Support Providers</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Role</th>
                                                <th>Name</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Support/a:SupportProvider">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:ActorRole/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                            </section>
                        </component>
                    </xsl:if>
                    <xsl:if test="a:ContinuityOfCareRecord/a:References">
                        <component>
                            <section>
                                <title>References</title>
                                <text>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Location</th>
                                                <th>Source</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:References/a:Reference">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="a:Type/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="date:format-date">
                                                            <xsl:with-param name="date-time" select="a:DateTime/a:ExactDateTime"/>
                                                        </xsl:call-template>
                                                    </td>
                                                    <td>
                                                        <strong class="clinical">
                                                            <xsl:value-of select="a:Description/a:Text"/>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of select="a:Locations/a:Location/a:Description/a:Text"/>
                                                    </td>
                                                    <td>
                                                        <xsl:call-template name="actorName">
                                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                                        </xsl:call-template>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </text>
                            </section>
                        </component>
                    </xsl:if>
                    -->
                    <!-- -->
                    <component>
                        <section>
                            <title>Additional Information About People &amp; Organizations</title>
                            <text>
                                <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:Person]">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <th>Specialty</th>
                                                <th>Relation</th>
                                                <th>Identification Numbers</th>
                                                <th>Phone</th>
                                                <th>Address/ E-mail</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Actors/a:Actor">
                                                <xsl:sort select="a:Person/a:Name/a:DisplayName|a:Person/a:Name/a:CurrentName/a:Family" data-type="text" order="ascending"/>
                                                <xsl:if test="a:Person">
                                                    <tr>
                                                        <td>
                                                            <xsl:call-template name="actorName">
                                                                <xsl:with-param name="objID" select="a:ActorObjectID"/>
                                                            </xsl:call-template>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Specialty/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Relation/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:IDs">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:ID"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Telephone">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Address">
                                                                <xsl:if test="a:Type">
                                                                    <xsl:value-of select="a:Type/a:Text"/>
                                                                    <xsl:text>:</xsl:text>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line1">
                                                                    <xsl:value-of select="a:Line1"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line2">
                                                                    <xsl:value-of select="a:Line2"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:City">
                                                                    <xsl:value-of select="a:City"/>
                                                                    <xsl:text>, </xsl:text>
                                                                </xsl:if>
                                                                <xsl:value-of select="a:State"/>
                                                                <xsl:value-of select="a:PostalCode"/>
                                                                <br/>
                                                            </xsl:for-each>
                                                            <xsl:for-each select="a:EMail">
                                                                <br/>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                    </tr>
                                                </xsl:if>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </xsl:if>
                                <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:Organization]">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <th>Specialty</th>
                                                <th>Relation</th>
                                                <th>Identification Numbers</th>
                                                <th>Phone</th>
                                                <th>Address/ E-mail</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Actors/a:Actor">
                                                <xsl:sort select="a:Organization/a:Name" data-type="text" order="ascending"/>
                                                <xsl:if test="a:Organization">
                                                    <tr>
                                                        <td>
                                                            <xsl:value-of select="a:Organization/a:Name"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Specialty/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:Relation/a:Text"/>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:IDs">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:ID"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Telephone">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Address">
                                                                <xsl:if test="a:Type">
                                                                    <xsl:value-of select="a:Type/a:Text"/>
                                                                    <xsl:text>:</xsl:text>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line1">
                                                                    <xsl:value-of select="a:Line1"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line2">
                                                                    <xsl:value-of select="a:Line2"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:City">
                                                                    <xsl:value-of select="a:City"/>
                                                                    <xsl:text>, </xsl:text>
                                                                </xsl:if>
                                                                <xsl:value-of select="a:State"/>
                                                                <xsl:value-of select="a:PostalCode"/>
                                                                <br/>
                                                            </xsl:for-each>
                                                            <xsl:for-each select="a:EMail">
                                                                <br/>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                    </tr>
                                                </xsl:if>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </xsl:if>
                                <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:InformationSystem]">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Version</th>
                                                <th>Identification Numbers</th>
                                                <th>Phone</th>
                                                <th>Address/ E-mail</th>
                                            </tr>
                                            <xsl:for-each select="/a:ContinuityOfCareRecord/a:Actors/a:Actor">
                                                <xsl:sort select="a:InformationSystem/a:Name" data-type="text" order="ascending"/>
                                                <xsl:if test="a:InformationSystem">
                                                    <tr>
                                                        <td>
                                                            <xsl:value-of select="a:InformationSystem/a:Name"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:InformationSystem/a:Type"/>
                                                        </td>
                                                        <td>
                                                            <xsl:value-of select="a:InformationSystem/a:Version"/>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:IDs">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:ID"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Telephone">
                                                                <xsl:value-of select="a:Type/a:Text"/>
                                                                <xsl:text>: </xsl:text>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                        <td>
                                                            <xsl:for-each select="a:Address">
                                                                <xsl:if test="Type">
                                                                    <xsl:value-of select="a:Type/a:Text"/>
                                                                    <xsl:text>:</xsl:text>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line1">
                                                                    <xsl:value-of select="a:Line1"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:Line2">
                                                                    <xsl:value-of select="a:Line2"/>
                                                                    <br/>
                                                                </xsl:if>
                                                                <xsl:if test="a:City">
                                                                    <xsl:value-of select="a:City"/>
                                                                    <xsl:text>, </xsl:text>
                                                                </xsl:if>
                                                                <xsl:value-of select="a:State"/>
                                                                <xsl:value-of select="a:PostalCode"/>
                                                                <br/>
                                                            </xsl:for-each>
                                                            <xsl:for-each select="a:EMail">
                                                                <br/>
                                                                <xsl:value-of select="a:Value"/>
                                                            </xsl:for-each>
                                                        </td>
                                                    </tr>
                                                </xsl:if>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </xsl:if>
                            </text>
                        </section>
                    </component>
                    <!-- -->
                </structuredBody>
            </component>
        </ClinicalDocument>
    </xsl:template>

    <xsl:template name="ccdAssignedEntity">
        <xsl:param name="ccrActorObjectID"/>

        <xsl:variable name="CCRActor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrActorObjectID]"/>

        <assignedEntity>
            <xsl:call-template name="ccdEntityID">
                <xsl:with-param name="CCRActor" select="$CCRActor"/>
            </xsl:call-template>

            <xsl:call-template name="ccdAddress">
                <xsl:with-param name="CCRActorAddress" select="$CCRActor/a:Address"/>
            </xsl:call-template>

            <xsl:call-template name="ccdTelecom">
                <xsl:with-param name="CCRActor" select="$CCRActor"/>
            </xsl:call-template>

            <xsl:if test="$CCRActor/a:Person">
                <xsl:call-template name="ccdPerson">
                    <xsl:with-param name="CCRActorPerson" select="$CCRActor/a:Person"/>
                    <xsl:with-param name="personNodeName" select="'assignedPerson'"/>
                </xsl:call-template>
            </xsl:if>

            <xsl:if test="$CCRActor/a:InternalCCRLink[a:LinkRelationship='Organization']">
                <xsl:call-template name="ccdOrganization">
                    <xsl:with-param name="ccrActorObjectID" select="$CCRActor/a:InternalCCRLink[a:LinkRelationship='Organization']/a:LinkID"/>
                    <xsl:with-param name="organizationNodeName" select="'representedOrganization'"/>
                </xsl:call-template>
            </xsl:if>

        </assignedEntity>
    </xsl:template>

    <xsl:template name="ccdPatient">
        <xsl:param name="CCRActorPerson"/>
        <patient>
            <xsl:call-template name="ccdPersonName">
                <xsl:with-param name="CCRActorName" select="$CCRActorPerson/a:Name"/>
            </xsl:call-template>

            <xsl:if test="$CCRActorPerson/a:Gender">
                <xsl:call-template name="ccdCodedValue">
                    <xsl:with-param name="ccrCodedDescription" select="$CCRActorPerson/a:Gender"/>
                    <xsl:with-param name="domain" select="'AdministrativeGender'"/>
                    <xsl:with-param name="nodeName" select="'administrativeGenderCode'"/>
                </xsl:call-template>
            </xsl:if>

            <xsl:if test="$CCRActorPerson/a:DateOfBirth">
                <birthTime>
                    <xsl:attribute name="value">
                        <xsl:call-template name="date:format-date">
                            <xsl:with-param name="date-time" select="$CCRActorPerson/a:DateOfBirth/a:ExactDateTime"/>
                            <xsl:with-param name="pattern">yyyyMMddhhmmss</xsl:with-param>
                        </xsl:call-template>
                    </xsl:attribute>
                </birthTime>
            </xsl:if>

            <xsl:if test="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement[a:Type/a:Text='Marital Status']">
                <xsl:call-template name="ccdCodedValue">
                    <xsl:with-param name="ccrCodedDescription" select="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement[a:Type/a:Text='Marital Status']/a:Description"/>
                    <xsl:with-param name="domain" select="'MaritalStatus'"/>
                    <xsl:with-param name="nodeName" select="'maritalStatusCode'"/>
                </xsl:call-template>
            </xsl:if>

            <xsl:if test="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement[a:Type/a:Text='Language']">
                <languageCommunication>
                    <templateId root="2.16.840.1.113883.3.88.11.83.2" assigningAuthorityName="HITSP/C83"/>
                    <templateId root="1.3.6.1.4.1.19376.1.5.3.1.2.1" assigningAuthorityName="IHE/PCC"/>
                    <xsl:call-template name="ccdCodedValue">
                        <xsl:with-param name="ccrCodedDescription" select="/a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement[a:Type/a:Text='Language']/a:Description"></xsl:with-param>
                        <xsl:with-param name="domain" select="'HumanLanguage'"/>
                        <xsl:with-param name="nodeName" select="'languageCode'"/>
                    </xsl:call-template>
                </languageCommunication>
            </xsl:if>
        </patient>
    </xsl:template>

    <xsl:template name="ccdPerformer">
        <xsl:param name="ccrActorReference"/>
        <performer typeCode="PRF">
            <xsl:if test="$ccrActorReference/a:ActorRole">
                <xsl:call-template name="ccdCodedValue">
                    <xsl:with-param name="ccrCodedDescription" select="$ccrActorReference/a:ActorRole"/>
                    <xsl:with-param name="nodeName" select="'functionCode'"/>
                </xsl:call-template>
            </xsl:if>
            <xsl:call-template name="ccdAssignedEntity">
                <xsl:with-param name="ccrActorObjectID" select="$ccrActorReference/a:ActorID"/>
            </xsl:call-template>
        </performer>
    </xsl:template>

    <xsl:template name="ccdPerson">
        <xsl:param name="CCRActorPerson"/>
        <xsl:param name="personNodeName">assignedPerson</xsl:param>
        <xsl:element name="{$personNodeName}">
            <xsl:call-template name="ccdPersonName">
                <xsl:with-param name="CCRActorName" select="$CCRActorPerson/a:Name"/>
            </xsl:call-template>
        </xsl:element>
    </xsl:template>

    <xsl:template name="ccdPersonName">
        <xsl:param name="CCRActorName"/>
        <name>
            <xsl:if test="$CCRActorName/a:CurrentName/a:Title">
                <prefix>
                    <xsl:value-of select="$CCRActorName/a:CurrentName/a:Title"/>
                </prefix>
            </xsl:if>
            <xsl:if test="$CCRActorName/a:CurrentName/a:Given">
                <given>
                    <xsl:value-of select="$CCRActorName/a:CurrentName/a:Given"/>
                </given>
            </xsl:if>
            <xsl:if test="$CCRActorName/a:CurrentName/a:Middle">
                <given>
                    <xsl:value-of select="$CCRActorName/a:CurrentName/a:Middle"/>
                </given>
            </xsl:if>
            <xsl:if test="$CCRActorName/a:CurrentName/a:Family">
                <family>
                    <xsl:value-of select="$CCRActorName/a:CurrentName/a:Family"/>
                </family>
            </xsl:if>
            <xsl:if test="$CCRActorName/a:CurrentName/a:Suffix">
                <suffix>
                    <xsl:value-of select="$CCRActorName/a:CurrentName/a:Suffix"/>
                </suffix>
            </xsl:if>
        </name>
    </xsl:template>

    <xsl:template name="ccdOrganization">
        <xsl:param name="ccrActorObjectID"/>
        <xsl:param name="organizationNodeName"/>

        <xsl:variable name="CCRActor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrActorObjectID]"/>

        <xsl:element name="{$organizationNodeName}">
            <xsl:call-template name="ccdEntityID">
                <xsl:with-param name="CCRActor" select="$CCRActor"/>
            </xsl:call-template>
            <xsl:call-template name="ccdTelecom">
                <xsl:with-param name="CCRActor" select="$CCRActor"/>
            </xsl:call-template>

            <xsl:call-template name="ccdAddress">
                <xsl:with-param name="CCRActorAddress" select="$CCRActor/a:Address[1]"/>
            </xsl:call-template>
        </xsl:element>
    </xsl:template>

    <xsl:template name="ccdPatientRole">
        <xsl:param name="ccrActorObjectID"/>

        <xsl:variable name="CCRActor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrActorObjectID]"/>

        <xsl:call-template name="ccdEntityID">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:call-template name="ccdAddress">
            <xsl:with-param name="CCRActorAddress" select="$CCRActor/a:Address[1]"/>
        </xsl:call-template>

        <xsl:call-template name="ccdTelecom">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:call-template name="ccdPatient">
            <xsl:with-param name="CCRActorPerson" select="$CCRActor/a:Person"/>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="ccdAssignedAuthor">
        <xsl:param name="ccrActorObjectID"/>
		<assignedAuthor>
        <xsl:variable name="CCRActor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrActorObjectID]"/>

        <xsl:call-template name="ccdEntityID">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:call-template name="ccdAddress">
            <xsl:with-param name="CCRActorAddress" select="$CCRActor/a:Address[1]"/>
        </xsl:call-template>

        <xsl:call-template name="ccdTelecom">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:call-template name="ccdPerson">
            <xsl:with-param name="CCRActorPerson" select="$CCRActor/a:Person"/>
        </xsl:call-template>

        <xsl:if test="$CCRActor/a:InternalCCRLink[a:LinkRelationship='Organization']">
            <representedOrganization>
                <xsl:call-template name="ccdOrganization">
                    <xsl:with-param name="ccrActorObjectID" select="$CCRActor/a:InternalCCRLink[a:LinkRelationship='Organization']/a:LinkID"/>
                </xsl:call-template>
            </representedOrganization>
        </xsl:if>
        </assignedAuthor>
    </xsl:template>

    <xsl:template name="ccdParticipantRoleActor">
        <xsl:param name="ccrActorObjectID"/>

        <xsl:variable name="CCRActor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$ccrActorObjectID]"/>

        <xsl:call-template name="ccdEntityID">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:call-template name="ccdAddress">
            <xsl:with-param name="CCRActorAddress" select="$CCRActor/a:Address[1]"/>
        </xsl:call-template>

        <xsl:call-template name="ccdTelecom">
            <xsl:with-param name="CCRActor" select="$CCRActor"/>
        </xsl:call-template>

        <xsl:if test="$CCRActor/a:Organization/a:Name">
            <playingEntity classCode="PLC">
                <name>
                    <xsl:value-of select="$CCRActor/a:Organization/a:Name"/>
                </name>
            </playingEntity>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ccdParticipantRoleCodedDescription">
        <xsl:param name="ccrCodedDescription"/>
        <participantRole classCode="MANU">
           <!--Product Detail-->
           <playingEntity classCode="MMAT">
				<xsl:if test="$ccrCodedDescription/a:Code">
					<xsl:call-template name="ccdCodedValue">
						<xsl:with-param name="ccrCodedDescription" select="$ccrCodedDescription"/>
						<xsl:with-param name="nodeName" select="'code'"/>
					</xsl:call-template>
				</xsl:if>
				<name>
					<xsl:value-of select="$ccrCodedDescription/a:Text"/>
				</name>
			</playingEntity>
		</participantRole>
    </xsl:template>

    <xsl:template name="ccdEntityID">
        <xsl:param name="CCRActor"/>
        <id>
            <xsl:choose>
                <xsl:when test="$CCRActor/a:IDs">
                    <xsl:attribute name="extension">
                        <xsl:value-of select="$CCRActor/a:IDs[1]/a:ID"/>
                    </xsl:attribute>
                    <xsl:attribute name="root">
                        <xsl:value-of select="$CCRActor/a:IDs[1]/a:Type/a:Text"/>
                    </xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="extension">
                        <xsl:value-of select="$CCRActor/a:ActorObjectID"/>
                    </xsl:attribute>
                    <xsl:attribute name="root">
                        <xsl:text>CCRActorID</xsl:text>
                    </xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
        </id>
         <xsl:if test="$CCRActor/a:Organization">
            <name><xsl:value-of select="$CCRActor/a:Organization/a:Name"></xsl:value-of></name>
        </xsl:if>
    </xsl:template>		
    <xsl:template name="ccdAddress">
        <xsl:param name="CCRActorAddress"/>
        <addr>
            <xsl:choose>
                <xsl:when test="$CCRActorAddress">
                    <xsl:attribute name="use">
                        <xsl:call-template name="CDAVocabularyLookup">
                            <xsl:with-param name="domain" select="'telecommunicationsAddressUse'"/>
                            <xsl:with-param name="ccrtext" select="$CCRActorAddress/a:Type/a:Text"/>
                        </xsl:call-template>
                    </xsl:attribute>
                    <streetAddressLine>
                        <xsl:value-of select="$CCRActorAddress/a:Line1"/>
                    </streetAddressLine>
                    <city>
                        <xsl:value-of select="$CCRActorAddress/a:City"/>
                    </city>
                    <state>
                        <xsl:value-of select="$CCRActorAddress/a:State"/>
                    </state>
                    <postalCode>
                        <xsl:value-of select="$CCRActorAddress/a:PostalCode"/>
                    </postalCode>
                </xsl:when>
                <xsl:otherwise>
                    <streetAddressLine/>
                </xsl:otherwise>
            </xsl:choose>
        </addr>
    </xsl:template>
    
    <xsl:template name="ccdTelecom">
        <xsl:param name="CCRActor"/>
        <telecom>
            <xsl:if test="$CCRActor/a:Telephone">
                <xsl:attribute name="use">
                    <xsl:call-template name="CDAVocabularyLookup">
                        <xsl:with-param name="domain" select="'telecommunicationsAddressUse'"/>
                        <xsl:with-param name="ccrtext" select="$CCRActor/a:Telephone/a:Type/a:Text"/>
                    </xsl:call-template>
                </xsl:attribute>
                <xsl:attribute name="value">
                    <xsl:text>tel:+1-</xsl:text>
                    <xsl:value-of select="$CCRActor/a:Telephone[1]/a:Value"/>
                </xsl:attribute>
            </xsl:if>
        </telecom>

        <xsl:if test="$CCRActor/a:Email">
            <telecom>
                <xsl:choose>
                    <xsl:when test="$CCRActor/a:Telephone">
                        <xsl:attribute name="use">
                            <xsl:call-template name="CDAVocabularyLookup">
                                <xsl:with-param name="domain" select="'telecommunicationsAddressUse'"/>
                                <xsl:with-param name="ccrtext" select="$CCRActor/a:Telephone/a:Type/a:Text"/>
                            </xsl:call-template>
                        </xsl:attribute>
                        <xsl:attribute name="value">
                            <xsl:text>mailto:</xsl:text>
                            <xsl:value-of select="$CCRActor/a:Email[1]/a:Value"/>
                        </xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="value">
                            <xsl:text>Unknown</xsl:text>
                        </xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </telecom>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ccdID">
        <xsl:param name="ccrObjectID"/>
        <xsl:param name="suffix"/>
        <id>
            <xsl:attribute name="root">
                <xsl:value-of select="$ccrObjectID"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="extension">CCRObjectID</xsl:attribute>
        </id>
    </xsl:template>

    <xsl:template name="ccdCodedValue">
        <xsl:param name="ccrCodedDescription"/>
        <xsl:param name="type"/>
        <xsl:param name="domain"/>
        <xsl:param name="nodeName">value</xsl:param>
        <xsl:param name="originalTextReference"/>

        <xsl:element name="{$nodeName}">
            <xsl:if test="$type">
                <xsl:attribute name="xsi:type">
                    <xsl:value-of select="$type"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="$ccrCodedDescription/a:Code">
                    <xsl:attribute name="displayName">
                        <xsl:value-of select="$ccrCodedDescription/a:Text"/>
                    </xsl:attribute>
                    <xsl:attribute name="code">
                        <xsl:value-of select="$ccrCodedDescription/a:Code/a:Value"/>
                    </xsl:attribute>
                    <xsl:attribute name="codeSystemName">
                        <xsl:value-of select="$ccrCodedDescription/a:Code/a:CodingSystem"/>
                    </xsl:attribute>
                    <xsl:attribute name="codeSystem">
                        <xsl:call-template name="HL7OIDLookup">
                            <xsl:with-param name="name" select="$ccrCodedDescription/a:Code/a:CodingSystem"/>
                        </xsl:call-template>
                    </xsl:attribute>
                </xsl:when>
                <xsl:when test="$domain">
                    <xsl:variable name="cdaCode">
                        <xsl:call-template name="CDAVocabularyLookup">
                            <xsl:with-param name="domain" select="$domain"/>
                            <xsl:with-param name="ccrtext" select="$ccrCodedDescription/a:Text"/>
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:attribute name="displayName">
                        <xsl:call-template name="CDADisplayNameLookup">
                            <xsl:with-param name="domain" select="$domain"/>
                            <xsl:with-param name="cdacode" select="$cdaCode"/>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="code">
                        <xsl:value-of select="$cdaCode"></xsl:value-of>
                    </xsl:attribute>
                    <xsl:attribute name="codeSystemName">
                        <xsl:call-template name="CDAVocabularyCodeSystemNameLookup">
                            <xsl:with-param name="domain" select="$domain"/>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:attribute name="codeSystemName">
                        <xsl:call-template name="CDAVocabularyCodeSystemLookup">
                            <xsl:with-param name="domain" select="$domain"/>
                        </xsl:call-template>
                    </xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <originalText>
                        <xsl:value-of select="$ccrCodedDescription/a:Text"/>
                        <xsl:if test="$originalTextReference">
                            <reference>
                                <xsl:attribute name="value">
                                    <xsl:value-of select="$originalTextReference"/>
                                </xsl:attribute>
                            </reference>
                        </xsl:if>
                    </originalText>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
    </xsl:template>

    <xsl:template name="ccdDateTime">
        <xsl:param name="dt"/>
        <xsl:param name="type"/>
        <xsl:if test="$dt">
            <xsl:if test="$dt[1]/a:ExactDateTime">
                <effectiveTime>
                    <xsl:if test="$type">
                        <xsl:attribute name="xsi:type">
                            <xsl:value-of select="$type"></xsl:value-of>
                        </xsl:attribute>
                    </xsl:if>
                    <low>
                        <xsl:attribute name="value">
                            <xsl:call-template name="date:format-date">
                                <xsl:with-param name="date-time" select="$dt[1]/a:ExactDateTime"/>
                                <xsl:with-param name="pattern">yyyyMMdd</xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </low>
                    <high nullFlavor="UNK"/>
                </effectiveTime>
            </xsl:if>
            <xsl:if test="$dt[1]/a:DateTimeRange">
                <effectiveTime>
                    <low>
                        <xsl:attribute name="value">
                            <xsl:call-template name="date:format-date">
                                <xsl:with-param name="date-time" select="$dt[1]/a:DateTimeRange/a:BeginRange/a:ExactDateTime"/>
                                <xsl:with-param name="pattern">yyyyMMdd</xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </low>
                    <high>
                        <xsl:attribute name="value">
                            <xsl:call-template name="date:format-date">
                                <xsl:with-param name="date-time" select="$dt[1]/a:DateTimeRange/a:EndRange/a:ExactDateTime"/>
                                <xsl:with-param name="pattern">yyyyMMdd</xsl:with-param>
                            </xsl:call-template>
                        </xsl:attribute>
                    </high>
                </effectiveTime>
            </xsl:if>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ccdMedicationFrequency">
        <xsl:param name="frequency"/>
        <xsl:if test="$frequency">
            <xsl:choose>
                <xsl:when test="$frequency/a:Value='qd'">
                    <effectiveTime xsi:type="PIVL_TS" operator="A">
                        <xsl:attribute name="institutionSpecified">true</xsl:attribute>
                        <period>
                            <xsl:attribute name="value">24</xsl:attribute>
                            <xsl:attribute name="unit">h</xsl:attribute>
                        </period>
                    </effectiveTime>
                </xsl:when>
                <xsl:when test="$frequency/a:Value='bid'">
                    <effectiveTime xsi:type="PIVL_TS" operator="A">
                        <xsl:attribute name="institutionSpecified">true</xsl:attribute>
                        <period>
                            <xsl:attribute name="value">12</xsl:attribute>
                            <xsl:attribute name="unit">h</xsl:attribute>
                        </period>
                    </effectiveTime>
                </xsl:when>
                <xsl:when test="$frequency/a:Value='tid'">
                    <effectiveTime xsi:type="PIVL_TS" operator="A">
                        <xsl:attribute name="institutionSpecified">true</xsl:attribute>
                        <period>
                            <xsl:attribute name="value">8</xsl:attribute>
                            <xsl:attribute name="unit">h</xsl:attribute>
                        </period>
                    </effectiveTime>
                </xsl:when>
                <xsl:when test="$frequency/a:Value='qid'">
                    <effectiveTime xsi:type="PIVL_TS" operator="A">
                        <xsl:attribute name="institutionSpecified">true</xsl:attribute>
                        <period>
                            <xsl:attribute name="value">6</xsl:attribute>
                            <xsl:attribute name="unit">h</xsl:attribute>
                        </period>
                    </effectiveTime>
                </xsl:when>
                <xsl:when test="$frequency/a:Value='qam'">
                    <effectiveTime xsi:type='EIVL' operator='A'>
                        <event code='ACM'/>
                    </effectiveTime>
                </xsl:when>
                <xsl:when test="$frequency/a:Value='qpm'">
                    <effectiveTime xsi:type='EIVL' operator='A'>
                        <event code='PCV'/>
                    </effectiveTime>
                </xsl:when>
            </xsl:choose>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ccdStatus">
        <xsl:param name="ccrStatus"/>

        <entryRelationship typeCode="REFR">
            <observation classCode="OBS" moodCode="EVN">
                <templateId root="2.16.840.1.113883.10.20.1.50"/>
                <!-- Problem status observation template -->
                <code code="33999-4" codeSystem="2.16.840.1.113883.6.1" displayName="Status"/>
                <statusCode code="completed"/>
                <xsl:call-template name="ccdCodedValue">
                    <xsl:with-param name="ccrCodedDescription" select="$ccrStatus"/>
                </xsl:call-template>
            </observation>
        </entryRelationship>
    </xsl:template>

    <xsl:template name="ccdStatusObservation">
        <xsl:param name="status"/>
        <xsl:variable name="statusText" select="$status/a:Text"></xsl:variable>
        <xsl:choose>
            <xsl:when test="$statusText='Active'">
                <xsl:call-template name="ccdStatusElement">
                    <xsl:with-param name="statusCode" select="'55561003'"/>
                    <xsl:with-param name="statusDisplayName" select="$statusText"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="$statusText='Inactive'">
                <xsl:call-template name="ccdStatusElement">
                    <xsl:with-param name="statusCode" select="'73425007'"/>
                    <xsl:with-param name="statusDisplayName" select="$statusText"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="$statusText='Chronic'">
                <xsl:call-template name="ccdStatusElement">
                    <xsl:with-param name="statusCode" select="'90734009'"/>
                    <xsl:with-param name="statusDisplayName" select="$statusText"/>
                </xsl:call-template>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="ccdStatusProcedure">
        <xsl:param name="status"/>
        <xsl:variable name="statusText" select="$status/a:Text"></xsl:variable>
        <xsl:choose>
            <xsl:when test="$statusText='Completed'">
                <statusCode code="completed"/>
            </xsl:when>
            <xsl:when test="$statusText='Active'">
                <statusCode code="active"/>
            </xsl:when>
            <xsl:when test="$statusText='Aborted'">
                <statusCode code="aborted"/>
            </xsl:when>
            <xsl:when test="$statusText='Cancelled' or $statusText='Canceled'">
                <statusCode code="cancelled"/>
            </xsl:when>
            <xsl:otherwise>
                <statusCode>
                    <xsl:attribute name="code">
                        <xsl:value-of select="$statusText"></xsl:value-of>
                    </xsl:attribute>
                </statusCode>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="ccdStatusElement">
        <xsl:param name="statusCode"/>
        <xsl:param name="statusDisplayName"/>
        <value>
            <xsl:attribute name="xsi:type">CE</xsl:attribute>
            <xsl:attribute name="code">
                <xsl:value-of select="$statusCode"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="codeSystem">2.16.840.1.113883.6.96</xsl:attribute>
            <xsl:attribute name="displayName">
                <xsl:value-of select="$statusDisplayName"></xsl:value-of>
            </xsl:attribute>
        </value>
    </xsl:template>

    <xsl:template name="ccdObservation">
        <xsl:param name="ccrTestNode"/>
        <xsl:param name="testDate"/>

        <component>
            <observation classCode="OBS" moodCode="EVN">
                <templateId root="2.16.840.1.113883.3.88.11.83.15" assigningAuthorityName="HITSP C83"/>
                <templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
                <templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>

                <!-- Result observation template -->

                <xsl:call-template name="ccdID">
                    <xsl:with-param name="ccrObjectID" select="$ccrTestNode/a:CCRDataObjectID"/>
                </xsl:call-template>

                <!-- <code> -->
                <xsl:call-template name="ccdCodedValue">
                    <xsl:with-param name="ccrCodedDescription" select="$ccrTestNode/a:Description"/>
                    <xsl:with-param name="nodeName" select="'code'"/>
                </xsl:call-template>

                <text>
                    <reference>
                        <xsl:attribute name="value">
                            <xsl:text>#</xsl:text>
                            <xsl:value-of select="$ccrTestNode/a:CCRDataObjectID"/>
                        </xsl:attribute>
                    </reference>
                </text>

                <statusCode code="completed"/>

                <!-- <effectiveTime> -->
                <xsl:choose>
                    <xsl:when test="$ccrTestNode/a:DateTime">
                        <xsl:call-template name="ccdDateTime">
                            <xsl:with-param name="dt" select="$ccrTestNode/a:DateTime"/>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:call-template name="ccdDateTime">
                            <xsl:with-param name="dt" select="$testDate"/>
                        </xsl:call-template>
                    </xsl:otherwise>
                </xsl:choose>

                <xsl:choose>
                    <xsl:when test="$ccrTestNode/a:TestResult[a:Value!='']">
                        <value xsi:type="PQ">
                            <xsl:attribute name="value">
                                <xsl:value-of select="$ccrTestNode/a:TestResult/a:Value"/>
                            </xsl:attribute>
                            <xsl:attribute name="unit">
                                <xsl:value-of select="$ccrTestNode/a:TestResult/a:Units/a:Unit"/>
                            </xsl:attribute>
                        </value>
                    </xsl:when>
                    <xsl:when test="$ccrTestNode/a:TestResult[a:Description/a:Text!='']">
                        <value xsi:type="TX">
                            <xsl:attribute name="value">
                                <xsl:value-of select="$ccrTestNode/a:TestResult/a:Description/a:Text"/>
                            </xsl:attribute>
                        </value>
                    </xsl:when>
                </xsl:choose>

                <xsl:if test="$ccrTestNode/a:Flag">
                    <xsl:call-template name="ccdCodedValue">
                        <xsl:with-param name="ccrCodedDescription" select="$ccrTestNode/a:Flag"/>
                        <xsl:with-param name="nodeName" select="'interpretationCode'"/>
                        <xsl:with-param name="domain" select="'ObservationInterpretation'"/>
                    </xsl:call-template>
                </xsl:if>

                <xsl:if test="$ccrTestNode/a:NormalResult">
                    <referenceRange>
                        <xsl:if test="$ccrTestNode/a:NormalResult/a:Normal/a:Description/a:Text">
                            <observationRange>
                                <text>
                                    <xsl:value-of select="$ccrTestNode/a:NormalResult/a:Normal/a:Description/a:Text"/>
                                </text>
                            </observationRange>
                        </xsl:if>
                    </referenceRange>
                </xsl:if>

            </observation>
        </component>

    </xsl:template>
</xsl:stylesheet>
