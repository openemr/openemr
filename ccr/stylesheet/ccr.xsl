<?xml version="1.0" encoding="UTF-8"?>
<!-- 

   Copyright 2007 American Academy of Family Physicians 
   
   Licensed under the Apache License, Version 2.0 (the "License"); 
   you may not use this file except in compliance with the License. 
   You may obtain a copy of the License at 
   
   http://www.apache.org/licenses/LICENSE-2.0 
   
   Unless required by applicable law or agreed to in writing, software distributed
   under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR 
   CONDITIONS OF ANY KIND, either express or implied. See the License for the 
   specific language governing permissions and limitations under the License.
   
This XSLT creates a simple HTML representation of the ASTM Continuity of Care Record. 
This representation does not present all the potential data storable in the CCR.  
Instead it gives a potential clinical representation of the CCR instance.  There is 
the potential for important information in a CCR to not be displayed in the resulting
HTML.  

Derived works MUST change the footer.xsl template to denote that the resulting HTML
is a derived work from the AAFP's XSLT or remove the display of the "American Academy
of Family Physicians" name.


Although not required, it is encouraged to submit modifications or improvements to
this XSLT back to the community.  

  Author:   	Steven E. Waldren, MD 
  		American Academy of Family Physicians
		swaldren@aafp.org

  Coauthors:	Ken Miller      Simon Sadedin
                Solventus       Medcommons

  Date: 	2007-06-01
  Version: 	2.0

 -->
 <xsl:stylesheet exclude-result-prefixes="a date str" version="1.0" xmlns:a="urn:astm-org:CCR" xmlns:date="http://exslt.org/dates-and-times" xmlns:str="http://exslt.org/strings" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output encoding="UTF-8" method="html"/>
  <!-- XSL Parameters -->
  <!-- This param can be used to define different CCS style sheets
			If not passed, the default will be used -->
  <xsl:param name="stylesheet"/>
  <xsl:template match="/">
    <html>
      <head>
        <!-- Load in the CSS file -->
        <xsl:choose>
          <xsl:when test="$stylesheet!=''">
            <link href="{$stylesheet}" rel="stylesheet" type="text/css"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="defaultCCS"/>
            <!-- call to ./templates/defaultCCS.xsl-->
          </xsl:otherwise>
        </xsl:choose>
        <title>Continuity of Care Record</title>
      </head>
      <body>
        <table cellPadding="1" cellSpacing="1">
          <tbody>
            <tr>
              <td>
                <table cellPadding="1" cellSpacing="1">
                  <tbody>
                    <tr id="ccrheaderrow">
                      <td>
                        <h1>Continuity of Care Record
			  <br/>
                        </h1>
                        <table bgColor="#ffffcc" cellPadding="1" cellSpacing="3" id="ccrheader" width="75%">
                          <tbody>
                            <tr>
                              <td>
                                <strong>Date Created:</strong>
                              </td>
                              <td>
                                <xsl:call-template name="date:format-date">
                                  <xsl:with-param name="date-time">
                                    <xsl:value-of select="a:ContinuityOfCareRecord/a:DateTime/a:ExactDateTime"/>
                                  </xsl:with-param>
                                  <xsl:with-param name="pattern">EEE MMM dd, yyyy 'at' hh:mm aa zzz</xsl:with-param>
                                </xsl:call-template>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <strong>From:</strong>
                              </td>
                              <td>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:From/a:ActorLink">
                                  <xsl:call-template name="actorName">
                                    <xsl:with-param name="objID" select="a:ActorID"/>
                                  </xsl:call-template>
                                  <xsl:if test="a:ActorRole/a:Text">
                                    <xsl:text xml:space="preserve"> (</xsl:text>
                                    <xsl:value-of select="a:ActorRole/a:Text"/>
                                    <xsl:text>)</xsl:text>
                                  </xsl:if>
                                  <br/>
                                </xsl:for-each>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <strong>To:</strong>
                              </td>
                              <td>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:To/a:ActorLink">
                                  <xsl:call-template name="actorName">
                                    <xsl:with-param name="objID" select="a:ActorID"/>
                                  </xsl:call-template>
                                  <xsl:if test="a:ActorRole/a:Text">
                                    <xsl:text xml:space="preserve"> (</xsl:text>
                                    <xsl:value-of select="a:ActorRole/a:Text"/>
                                    <xsl:text>)</xsl:text>
                                  </xsl:if>
                                  <br/>
                                </xsl:for-each>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <strong>Purpose:</strong>
                              </td>
                              <td>
                                <xsl:value-of select="a:ContinuityOfCareRecord/a:Purpose/a:Description/a:Text"/>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <br/>
                      </td>
                    </tr>
                    <tr id="demographicsrow">
                      <td>
                        <span class="header">Patient Demographics</span>
                        <br/>
                        <table class="list" id="demographics">
                          <tbody>
                            <tr>
                              <th>Name</th>
                              <th>Date of Birth</th>
                              <th>Gender</th>
                              <th>Identification Numbers</th>
                              <th>Address / Phone</th>
                            </tr>
                            <xsl:for-each select="a:ContinuityOfCareRecord/a:Patient">
                              <xsl:variable name="objID" select="a:ActorID"/>
                              <xsl:for-each select="/a:ContinuityOfCareRecord/a:Actors/a:Actor">
                                <xsl:variable name="thisObjID" select="a:ActorObjectID"/>
                                <xsl:if test="$objID = $thisObjID">
                                  <tr>
                                    <td>
                                      <xsl:call-template name="actorName">
                                        <xsl:with-param name="objID">
                                          <xsl:value-of select="$thisObjID"/>
                                        </xsl:with-param>
                                      </xsl:call-template>
                                      <br/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:Person/a:DateOfBirth"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Person/a:Gender/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:IDs">
                                            <tr>
                                              <td width="50%">
                                                <xsl:value-of select="a:Type/a:Text"/>
                                              </td>
                                              <td width="50%">
                                                <xsl:value-of select="a:ID"/>
                                              </td>
                                            </tr>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:for-each select="a:Address">
                                        <xsl:if test="a:Type">
                                          <b>
                                            <xsl:value-of select="a:Type/a:Text"/>:</b>
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
                                          <xsl:value-of select="a:City"/>,
																				</xsl:if>
                                        <xsl:value-of select="a:State"/>
                                        <xsl:value-of select="a:PostalCode"/>
                                        <br/>
                                      </xsl:for-each>
                                      <xsl:for-each select="a:Telephone">
                                        <br/>
                                        <xsl:if test="a:Type/a:Text">
                                          <xsl:value-of select="a:Type/a:Text"/>:
																				</xsl:if>
                                        <xsl:value-of select="a:Value"/>
                                      </xsl:for-each>
                                    </td>
                                  </tr>
                                </xsl:if>
                              </xsl:for-each>
                            </xsl:for-each>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <span id="ccrcontent">
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Alerts">
                        <tr id="alertsrow">
                          <td>
                            <span class="header">Alerts</span>
                            <br/>
                            <table class="list" id="alerts">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Code</th>
                                  <th>Description</th>
                                  <th>Reaction</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Alerts/a:Alert">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Reaction/a:Description/a:Text"/>
                                      <xsl:if test="a:Reaction/a:Severity/a:Text">
                                        <xsl:text>-</xsl:text>
                                        <xsl:value-of select="a:Reaction/a:Severity/a:Text"/>
                                      </xsl:if>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:AdvanceDirectives">
                        <tr id="advancedirectivesrow">
                          <td>
                            <span class="header">Advance Directives</span>
                            <br/>
                            <table class="list" id="advancedirectives">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Description</th>
                                  <th>Status</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:AdvanceDirectives/a:AdvanceDirective">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Status/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Support">
                        <tr id="supportprovidersrow">
                          <td>
                            <span class="header" id="supportproviders">Support Providers</span>
                            <br/>
                            <table class="list">
                              <tbody>
                                <tr>
                                  <th>Role</th>
                                  <th>Name</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Support/a:SupportProvider">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:ActorRole/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:FunctionalStatus">
                        <tr id="functionalstatus">
                          <td>
                            <span class="header">Functional Status</span>
                            <br/>
                            <table class="list">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Code</th>
                                  <th>Description</th>
                                  <th>Status</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:FunctionalStatus/a:Function">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Status/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Problems">
                        <tr id="problemsrow">
                          <td>
                            <span class="header">Problems</span>
                            <br/>
                            <table class="list" id="problems">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Code</th>
                                  <th>Description</th>
                                  <th>Status</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Problems/a:Problem">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Status/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Procedures">
                        <tr id="proceduresrow">
                          <td>
                            <span class="header">Procedures</span>
                            <br/>
                            <table class="list" id="procedures">
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
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Procedures/a:Procedure">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:for-each select="a:Locations/a:Location">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                        <xsl:if test="a:Actor">
                                          <xsl:text>(</xsl:text>
                                          <xsl:call-template name="actorName">
                                            <xsl:with-param name="objID" select="a:Actor/a:ActorID"/>
                                          </xsl:call-template>
                                          <xsl:if test="a:Actor/a:ActorRole/a:Text">
                                            <xsl:text xml:space="preserve"> </xsl:text>-<xsl:text xml:space="preserve"> </xsl:text>
                                            <xsl:value-of select="a:ActorRole/a:Text"/>
                                            <xsl:text>)</xsl:text>
                                          </xsl:if>
                                        </xsl:if>
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
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Medications">
                        <tr id="medicationsrow">
                          <td>
                            <span class="header" id="medications">Medications</span>
                            <br/>
                            <table class="list">
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
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Medications/a:Medication">
                                  <tr>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Product/a:ProductName/a:Text"/>
                                        <xsl:if test="a:Product/a:BrandName">
                                          <xsl:text xml:space="preserve"> (</xsl:text>
                                          <xsl:value-of select="a:Product/a:BrandName/a:Text"/>
                                          <xsl:text>)</xsl:text>
                                        </xsl:if>
                                      </strong>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
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
                                      <table border="1" class="internal">
                                        <tbody>
                                          <xsl:apply-templates select="a:Directions"/>
                                          <!-- call to /templates/directions.xsl -->
                                        </tbody>
                                      </table>
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
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Immunizations">
                        <tr id="immunizationsrow">
                          <td>
                            <span class="header">Immunizations</span>
                            <br/>
                            <table class="list" id="immunizations">
                              <tbody>
                                <tr>
                                  <th>Code</th>
                                  <th>Vaccine</th>
                                  <th>Date</th>
                                  <th>Route</th>
                                  <th>Site</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Immunizations/a:Immunization">
                                  <tr>
                                    <td>
                                      <xsl:apply-templates select="a:Product/a:ProductName/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Product/a:ProductName/a:Text"/>
                                        <xsl:if test="a:Product/a:Form">
                                          <xsl:text xml:space="preserve"> (</xsl:text>
                                          <xsl:value-of select="a:Product/a:Form/a:Text"/>
                                          <xsl:text>)</xsl:text>
                                        </xsl:if>
                                      </strong>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Directions/a:Direction/a:Route/a:Text"/>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Directions/a:Direction/a:Site/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:VitalSigns">
                        <tr id="vitalsignsrow">
                          <td>
                            <span class="header">Vital Signs</span>
                            <br/>
                            <table class="list" id="vitalsigns">
                              <tbody>
                                <tr>
                                  <th>Vital Sign</th>
                                  <th>Date</th>
                                  <th>Result</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:VitalSigns/a:Result">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Description/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                            <xsl:with-param name="fmt">MMM dd, yyyy ':' hh:mm aa zzz</xsl:with-param>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:Test">
                                            <xsl:choose>
                                              <xsl:when test="position() mod 2=0">
                                                <tr class="even">
                                                  <td width="33%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:TestResult/a:Value"/>
                                                  <xsl:text xml:space="preserve"> </xsl:text>
                                                  <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:when>
                                              <xsl:otherwise>
                                                <tr class="odd">
                                                  <td width="33%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:TestResult/a:Value"/>
                                                  <xsl:text xml:space="preserve"> </xsl:text>
                                                  <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:otherwise>
                                            </xsl:choose>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Encounters">
                        <tr id="encountersrow">
                          <td>
                            <span class="header">Encounters</span>
                            <br/>
                            <table class="list" id="encounters">
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
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Encounters/a:Encounter">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
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
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
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
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:SocialHistory">
                        <tr id="socialhistoryrow">
                          <td>
                            <span class="header">Social History</span>
                            <br/>
                            <table class="list" id="socialhistory">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Code</th>
                                  <th>Description</th>
                                  <th>Status</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:SocialHistory/a:SocialHistoryElement">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <span>
                                          <xsl:value-of disable-output-escaping="yes" select="a:Description/a:Text"/>
                                        </span>
                                      </strong>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Status/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:FamilyHistory">
                        <tr id="familyhistoryrow">
                          <td>
                            <span class="header">Family History</span>
                            <br/>
                            <table class="list" id="familyhistory">
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
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:FamilyHistory/a:FamilyProblemHistory">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:apply-templates select="a:Problem/a:Description/a:Code"/>
                                    </td>
                                    <td>
                                      <table class="internal" id="familyhistoryproblem">
                                        <xsl:for-each select="a:Problem">
                                          <tr>
                                            <td>
                                              <strong class="clinical">
                                                <xsl:value-of select="a:Description/a:Text"/>
                                              </strong>
                                            </td>
                                          </tr>
                                        </xsl:for-each>
                                      </table>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:FamilyMember/a:ActorRole/a:Text"/>
                                    </td>
                                    <td>
                                      <xsl:value-of select="a:Status/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Results/a:Result[a:Test/a:TestResult/a:Value!='']">
                        <tr id="resultsrow">
                          <td>
                            <span class="header">Results (Discrete)</span>
                            <br/>
                            <table class="list" id="results">
                              <tbody>
                                <tr>
                                  <th>Test</th>
                                  <th>Date</th>
                                  <th>Result</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Results/a:Result[a:Test/a:TestResult/a:Value!='']">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Description/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                            <xsl:with-param name="fmt">MMM dd, yyyy ':' hh:mm aa zzz</xsl:with-param>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:Test">
                                            <xsl:choose>
                                              <xsl:when test="position() mod 2=0">
                                                <tr class="even">
                                                  <td width="33%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:TestResult/a:Value"/>
                                                  <xsl:text xml:space="preserve"> </xsl:text>
                                                  <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:when>
                                              <xsl:otherwise>
                                                <tr class="odd">
                                                  <td width="33%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:TestResult/a:Value"/>
                                                  <xsl:text xml:space="preserve"> </xsl:text>
                                                  <xsl:value-of select="a:TestResult/a:Units/a:Unit"/>
                                                  </td>
                                                  <td width="33%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:otherwise>
                                            </xsl:choose>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Results/a:Result[a:Test/a:TestResult/a:Description/a:Text!='']">
                        <tr id="resultsreportrow">
                          <td>
                            <span class="header">Results (Report)</span>
                            <br/>
                            <table class="list" id="resultsreport">
                              <tbody>
                                <tr>
                                  <th>Test</th>
                                  <th>Date</th>
                                  <th>Result</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Results/a:Result[a:Test/a:TestResult/a:Description/a:Text!='']">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Description/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                            <xsl:with-param name="fmt">MMM dd, yyyy ':' hh:mm aa zzz</xsl:with-param>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:Test">
                                            <xsl:choose>
                                              <xsl:when test="position() mod 2=0">
                                                <tr class="even">
                                                  <td width="20%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="65%">
                                                  <span>
                                                  <xsl:value-of disable-output-escaping="yes" select="a:TestResult/a:Description/a:Text"/>
                                                  </span>
                                                  </td>
                                                  <td width="15%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:when>
                                              <xsl:otherwise>
                                                <tr class="odd">
                                                  <td width="20%">
                                                  <strong class="clinical">
                                                  <xsl:value-of select="a:Description/a:Text"/>
                                                  </strong>
                                                  </td>
                                                  <td width="65%">
                                                  <xsl:value-of disable-output-escaping="yes" select="a:TestResult/a:Description/a:Text"/>
                                                  </td>
                                                  <td width="15%">
                                                  <xsl:value-of select="a:Flag/a:Text"/>
                                                  </td>
                                                </tr>
                                              </xsl:otherwise>
                                            </xsl:choose>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:Payers">
                        <tr id="insurancerow">
                          <td>
                            <span class="header">Insurance</span>
                            <br/>
                            <table class="list" id="insurance">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Identification Numbers</th>
                                  <th>Payment Provider</th>
                                  <th>Subscriber</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:Payers/a:Payer">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:DateTime">
                                            <xsl:call-template name="dateTime">
                                              <xsl:with-param name="dt" select="."/>
                                              <xsl:with-param name="fmt">MMM dd, yyyy ':' hh:mm aa zzz</xsl:with-param>
                                            </xsl:call-template>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <table border="1" class="internal">
                                        <tbody>
                                          <xsl:for-each select="a:IDs">
                                            <xsl:choose>
                                              <xsl:when test="position() mod 2=0">
                                                <tr class="even">
                                                  <td width="50%">
                                                  <xsl:value-of select="a:Type/a:Text"/>:</td>
                                                  <td width="50%">
                                                  <xsl:value-of select="a:ID"/>
                                                  </td>
                                                </tr>
                                              </xsl:when>
                                              <xsl:otherwise>
                                                <tr class="odd">
                                                  <td width="50%">
                                                  <xsl:value-of select="a:Type/a:Text"/>:</td>
                                                  <td width="50%">
                                                  <xsl:value-of select="a:ID"/>
                                                  </td>
                                                </tr>
                                              </xsl:otherwise>
                                            </xsl:choose>
                                          </xsl:for-each>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:PaymentProvider/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Subscriber/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare">
                        <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Treatment Recommendation']">
                          <tr id="planofcarerow">
                            <td>
                              <span class="header">Plan Of Care Recommendations</span>
                              <br/>
                              <table class="list" id="planofcare">
                                <tbody>
                                  <tr>
                                    <th>Description</th>
                                    <th>Recommendation</th>
                                    <th>Goal</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                  </tr>
                                  <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Treatment Recommendation']">
                                    <tr>
                                      <td>
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </td>
                                      <td>
                                        <xsl:value-of disable-output-escaping="yes" select="a:OrderRequest/a:Description/a:Text"/>
                                      </td>
                                      <td>
                                        <xsl:value-of disable-output-escaping="yes" select="a:OrderRequest/a:Goals/a:Goal/a:Description/a:Text"/>
                                      </td>
                                      <td>
                                        <xsl:value-of select="a:Status/a:Text"/>
                                      </td>
                                      <td>
                                        <a>
                                          <xsl:attribute name="href">
                                            <xsl:text>#</xsl:text>
                                            <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                          </xsl:attribute>
                                          <xsl:call-template name="actorName">
                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                          </xsl:call-template>
                                        </a>
                                      </td>
                                    </tr>
                                  </xsl:for-each>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </xsl:if>
                        <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Order']">
                          <tr id="planofcareordersrow">
                            <td>
                              <span class="header">Plan Of Care Orders</span>
                              <br/>
                              <table class="list" id="planofcareorders">
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
                                  <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:PlanOfCare/a:Plan[a:Type/a:Text='Order']">
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
                                        <table class="internal">
                                          <tbody>
                                            <xsl:call-template name="dateTime">
                                              <xsl:with-param name="dt" select="a:OrderRequest/a:Procedures/a:Procedure/a:DateTime"/>
                                            </xsl:call-template>
                                          </tbody>
                                        </table>
                                      </td>
                                      <td>
                                        <xsl:apply-templates select="a:OrderRequest/a:Procedures/a:Procedure/a:Description/a:Text"/>
                                      </td>
                                      <td>
                                        <span>Every </span>
                                        <xsl:apply-templates select="a:OrderRequest/a:Procedures/a:Procedure/a:Interval/a:Value"/>
                                        <xsl:text xml:space="preserve"> </xsl:text>
                                        <xsl:value-of select="a:OrderRequest/a:Procedures/a:Procedure/a:Interval/a:Units/a:Unit"/>
                                        <span> for </span>
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
                                        <a>
                                          <xsl:attribute name="href">
                                            <xsl:text>#</xsl:text>
                                            <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                          </xsl:attribute>
                                          <xsl:call-template name="actorName">
                                            <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                          </xsl:call-template>
                                        </a>
                                      </td>
                                    </tr>
                                  </xsl:for-each>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </xsl:if>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:Body/a:HealthCareProviders">
                        <tr id="healthcareprovidersrow">
                          <td>
                            <span class="header">Health Care Providers</span>
                            <br/>
                            <table class="list" id="healthcareproviders">
                              <tbody>
                                <tr>
                                  <th>Role</th>
                                  <th>Name</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:Body/a:HealthCareProviders/a:Provider">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:ActorRole/a:Text"/>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                      <xsl:if test="a:ContinuityOfCareRecord/a:References">
                        <tr id="referencesrow">
                          <td>
                            <span class="header">References</span>
                            <br/>
                            <table class="list" id="references">
                              <tbody>
                                <tr>
                                  <th>Type</th>
                                  <th>Date</th>
                                  <th>Description</th>
                                  <th>Location</th>
                                  <th>Source</th>
                                </tr>
                                <xsl:for-each select="a:ContinuityOfCareRecord/a:References/a:Reference">
                                  <tr>
                                    <td>
                                      <xsl:value-of select="a:Type/a:Text"/>
                                    </td>
                                    <td>
                                      <table class="internal">
                                        <tbody>
                                          <xsl:call-template name="dateTime">
                                            <xsl:with-param name="dt" select="a:DateTime"/>
                                          </xsl:call-template>
                                        </tbody>
                                      </table>
                                    </td>
                                    <td>
                                      <strong class="clinical">
                                        <xsl:value-of select="a:Description/a:Text"/>
                                      </strong>
                                    </td>
                                    <td>
                                      <a target="_blank">
                                        <xsl:attribute name="href">
                                          <xsl:value-of select="a:Locations/a:Location/a:Description/a:Text"/>
                                        </xsl:attribute>
                                        <xsl:value-of select="a:Locations/a:Location/a:Description/a:Text"/>
                                      </a>
                                    </td>
                                    <td>
                                      <a>
                                        <xsl:attribute name="href">
                                          <xsl:text>#</xsl:text>
                                          <xsl:value-of select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:attribute>
                                        <xsl:call-template name="actorName">
                                          <xsl:with-param name="objID" select="a:Source/a:Actor/a:ActorID"/>
                                        </xsl:call-template>
                                      </a>
                                    </td>
                                  </tr>
                                </xsl:for-each>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </xsl:if>
                    </span>
                    <tr>
                      <td/>
                      <td/>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
            <tr>
              <td/>
            </tr>
          </tbody>
        </table>
        <br/>
        <span id="actors">
          <span class="header">Additional Information About People &amp; Organizations</span>
          <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:Person]">
            <span id="people">
              <h4>People</h4>
              <table class="list" id="actorstable">
                <tbody>
                  <tr>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Relation</th>
                    <th>Identification Numbers</th>
                    <th>Phone</th>
                    <th>Address/ E-mail</th>
                  </tr>
                  <xsl:for-each select="a:ContinuityOfCareRecord/a:Actors/a:Actor">
                    <xsl:sort data-type="text" order="ascending" select="a:Person/a:Name/a:DisplayName|a:Person/a:Name/a:CurrentName/a:Family"/>
                    <xsl:if test="a:Person">
                      <tr>
                        <td>
                          <a>
                            <xsl:attribute name="name">
                              <xsl:value-of select="a:ActorObjectID"/>
                            </xsl:attribute>
                            <xsl:call-template name="actorName">
                              <xsl:with-param name="objID" select="a:ActorObjectID"/>
                            </xsl:call-template>
                          </a>
                        </td>
                        <td>
                          <xsl:value-of select="a:Specialty/a:Text"/>
                        </td>
                        <td>
                          <xsl:value-of select="a:Relation/a:Text"/>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:IDs">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:ID"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:Telephone">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:Value"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <xsl:for-each select="a:Address">
                            <xsl:if test="a:Type">
                              <b>
                                <xsl:value-of select="a:Type/a:Text"/>:</b>
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
                              <xsl:value-of select="a:City"/>,
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
            </span>
          </xsl:if>
          <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:Organization]">
            <span id="organizations">
              <h4>Organizations</h4>
              <table class="list" id="organizationstable">
                <tbody>
                  <tr>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Relation</th>
                    <th>Identification Numbers</th>
                    <th>Phone</th>
                    <th>Address/ E-mail</th>
                  </tr>
                  <xsl:for-each select="a:ContinuityOfCareRecord/a:Actors/a:Actor">
                    <xsl:sort data-type="text" order="ascending" select="a:Organization/a:Name"/>
                    <xsl:if test="a:Organization">
                      <tr>
                        <td>
                          <a>
                            <xsl:attribute name="name">
                              <xsl:value-of select="a:ActorObjectID"/>
                            </xsl:attribute>
                            <xsl:value-of select="a:Organization/a:Name"/>
                          </a>
                        </td>
                        <td>
                          <xsl:value-of select="a:Specialty/a:Text"/>
                        </td>
                        <td>
                          <xsl:value-of select="a:Relation/a:Text"/>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:IDs">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:ID"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:Telephone">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:Value"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <xsl:for-each select="a:Address">
                            <xsl:if test="a:Type">
                              <b>
                                <xsl:value-of select="a:Type/a:Text"/>:</b>
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
                              <xsl:value-of select="a:City"/>,
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
            </span>
          </xsl:if>
          <xsl:if test="a:ContinuityOfCareRecord/a:Actors/a:Actor[a:InformationSystem]">
            <span id="informationsystems">
              <h4>Information Systems</h4>
              <table class="list" id="informationsystemstable">
                <tbody>
                  <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Identification Numbers</th>
                    <th>Phone</th>
                    <th>Address/ E-mail</th>
                  </tr>
                  <xsl:for-each select="a:ContinuityOfCareRecord/a:Actors/a:Actor">
                    <xsl:sort data-type="text" order="ascending" select="a:InformationSystem/a:Name"/>
                    <xsl:if test="a:InformationSystem">
                      <tr>
                        <td>
                          <a>
                            <xsl:attribute name="name">
                              <xsl:value-of select="a:ActorObjectID"/>
                            </xsl:attribute>
                            <xsl:value-of select="a:InformationSystem/a:Name"/>
                          </a>
                        </td>
                        <td>
                          <xsl:value-of select="a:InformationSystem/a:Type"/>
                        </td>
                        <td>
                          <xsl:value-of select="a:InformationSystem/a:Version"/>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:IDs">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:ID"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <table class="internal">
                            <tbody>
                              <xsl:for-each select="a:Telephone">
                                <tr>
                                  <td width="50%">
                                    <xsl:value-of select="a:Type/a:Text"/>
                                  </td>
                                  <td width="50%">
                                    <xsl:value-of select="a:Value"/>
                                  </td>
                                </tr>
                              </xsl:for-each>
                            </tbody>
                          </table>
                        </td>
                        <td>
                          <xsl:for-each select="a:Address">
                            <xsl:if test="Type">
                              <b>
                                <xsl:value-of select="a:Type/a:Text"/>:</b>
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
                              <xsl:value-of select="a:City"/>,
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
            </span>
          </xsl:if>
        </span>
        <xsl:call-template name="footer"/>
      </body>
    </html>
  </xsl:template>
  <!-- date.format-date.template -->
  <!--  This is from the EXSLT.org Library (http://www.exslt.org/) -->
  <date:months>
    <date:month abbr="Jan" length="31">January</date:month>
    <date:month abbr="Feb" length="28">February</date:month>
    <date:month abbr="Mar" length="31">March</date:month>
    <date:month abbr="Apr" length="30">April</date:month>
    <date:month abbr="May" length="31">May</date:month>
    <date:month abbr="Jun" length="30">June</date:month>
    <date:month abbr="Jul" length="31">July</date:month>
    <date:month abbr="Aug" length="31">August</date:month>
    <date:month abbr="Sep" length="30">September</date:month>
    <date:month abbr="Oct" length="31">October</date:month>
    <date:month abbr="Nov" length="30">November</date:month>
    <date:month abbr="Dec" length="31">December</date:month>
  </date:months>
  <date:days>
    <date:day abbr="Sun">Sunday</date:day>
    <date:day abbr="Mon">Monday</date:day>
    <date:day abbr="Tue">Tuesday</date:day>
    <date:day abbr="Wed">Wednesday</date:day>
    <date:day abbr="Thu">Thursday</date:day>
    <date:day abbr="Fri">Friday</date:day>
    <date:day abbr="Sat">Saturday</date:day>
  </date:days>
  <xsl:template name="date:format-date">
    <xsl:param name="date-time"/>
    <xsl:param name="pattern"/>
    <xsl:variable name="formatted">
      <xsl:choose>
        <xsl:when test="starts-with($date-time, '---')">
          <xsl:call-template name="date:_format-date">
            <xsl:with-param name="year" select="'NaN'"/>
            <xsl:with-param name="month" select="'NaN'"/>
            <xsl:with-param name="day" select="number(substring($date-time, 4, 2))"/>
            <xsl:with-param name="pattern" select="$pattern"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="starts-with($date-time, '--')">
          <xsl:call-template name="date:_format-date">
            <xsl:with-param name="year" select="'NaN'"/>
            <xsl:with-param name="month" select="number(substring($date-time, 3, 2))"/>
            <xsl:with-param name="day" select="number(substring($date-time, 6, 2))"/>
            <xsl:with-param name="pattern" select="$pattern"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:variable name="neg" select="starts-with($date-time, '-')"/>
          <xsl:variable name="no-neg">
            <xsl:choose>
              <xsl:when test="$neg or starts-with($date-time, '+')">
                <xsl:value-of select="substring($date-time, 2)"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$date-time"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <xsl:variable name="no-neg-length" select="string-length($no-neg)"/>
          <xsl:variable name="timezone">
            <xsl:choose>
              <xsl:when test="substring($no-neg, $no-neg-length) = 'Z'">Z</xsl:when>
              <xsl:otherwise>
                <xsl:variable name="tz" select="substring($no-neg, $no-neg-length - 5)"/>
                <xsl:value-of select="$tz"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <xsl:if test="not(string($timezone)) or                           $timezone = 'Z' or                            (substring($timezone, 2, 2) &lt;= 23 and                            substring($timezone, 5, 2) &lt;= 59)">
            <xsl:variable name="dt" select="substring($no-neg, 1, $no-neg-length - string-length($timezone))"/>
            <xsl:variable name="dt-length" select="string-length($dt)"/>
            <xsl:choose>
              <xsl:when test="substring($dt, 3, 1) = ':' and                                   substring($dt, 6, 1) = ':'">
                <xsl:variable name="hour" select="substring($dt, 1, 2)"/>
                <xsl:variable name="min" select="substring($dt, 4, 2)"/>
                <xsl:variable name="sec" select="substring($dt, 7)"/>
                <xsl:if test="$hour &lt;= 23 and                                    $min &lt;= 59 and                                    $sec &lt;= 60">
                  <xsl:call-template name="date:_format-date">
                    <xsl:with-param name="year" select="'NaN'"/>
                    <xsl:with-param name="month" select="'NaN'"/>
                    <xsl:with-param name="day" select="'NaN'"/>
                    <xsl:with-param name="hour" select="$hour"/>
                    <xsl:with-param name="minute" select="$min"/>
                    <xsl:with-param name="second" select="$sec"/>
                    <xsl:with-param name="timezone" select="$timezone"/>
                    <xsl:with-param name="pattern" select="$pattern"/>
                  </xsl:call-template>
                </xsl:if>
              </xsl:when>
              <xsl:otherwise>
                <xsl:variable name="year" select="substring($dt, 1, 4) * (($neg * -2) + 1)"/>
                <xsl:choose>
                  <xsl:when test="not(number($year))"/>
                  <xsl:when test="$dt-length = 4">
                    <xsl:call-template name="date:_format-date">
                      <xsl:with-param name="year" select="$year"/>
                      <xsl:with-param name="timezone" select="$timezone"/>
                      <xsl:with-param name="pattern" select="$pattern"/>
                    </xsl:call-template>
                  </xsl:when>
                  <xsl:when test="substring($dt, 5, 1) = '-'">
                    <xsl:variable name="month" select="substring($dt, 6, 2)"/>
                    <xsl:choose>
                      <xsl:when test="not($month &lt;= 12)"/>
                      <xsl:when test="$dt-length = 7">
                        <xsl:call-template name="date:_format-date">
                          <xsl:with-param name="year" select="$year"/>
                          <xsl:with-param name="month" select="$month"/>
                          <xsl:with-param name="timezone" select="$timezone"/>
                          <xsl:with-param name="pattern" select="$pattern"/>
                        </xsl:call-template>
                      </xsl:when>
                      <xsl:when test="substring($dt, 8, 1) = '-'">
                        <xsl:variable name="day" select="substring($dt, 9, 2)"/>
                        <xsl:if test="$day &lt;= 31">
                          <xsl:choose>
                            <xsl:when test="$dt-length = 10">
                              <xsl:call-template name="date:_format-date">
                                <xsl:with-param name="year" select="$year"/>
                                <xsl:with-param name="month" select="$month"/>
                                <xsl:with-param name="day" select="$day"/>
                                <xsl:with-param name="timezone" select="$timezone"/>
                                <xsl:with-param name="pattern" select="$pattern"/>
                              </xsl:call-template>
                            </xsl:when>
                            <xsl:when test="substring($dt, 11, 1) = 'T' and substring($dt, 14, 1) = ':' and substring($dt, 17, 1) = ':'">
                              <xsl:variable name="hour" select="substring($dt, 12, 2)"/>
                              <xsl:variable name="min" select="substring($dt, 15, 2)"/>
                              <xsl:variable name="sec" select="substring($dt, 18)"/>
                              <xsl:if test="$hour &lt;= 23 and $min &lt;= 59 and $sec &lt;= 60">
                                <xsl:call-template name="date:_format-date">
                                  <xsl:with-param name="year" select="$year"/>
                                  <xsl:with-param name="month" select="$month"/>
                                  <xsl:with-param name="day" select="$day"/>
                                  <xsl:with-param name="hour" select="$hour"/>
                                  <xsl:with-param name="minute" select="$min"/>
                                  <xsl:with-param name="second" select="$sec"/>
                                  <xsl:with-param name="timezone" select="$timezone"/>
                                  <xsl:with-param name="pattern" select="$pattern"/>
                                </xsl:call-template>
                              </xsl:if>
                            </xsl:when>
                          </xsl:choose>
                        </xsl:if>
                      </xsl:when>
                    </xsl:choose>
                  </xsl:when>
                </xsl:choose>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:if>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:value-of select="$formatted"/>
  </xsl:template>
  <xsl:template name="date:_format-date">
    <xsl:param name="year"/>
    <xsl:param name="month" select="1"/>
    <xsl:param name="day" select="1"/>
    <xsl:param name="hour" select="0"/>
    <xsl:param name="minute" select="0"/>
    <xsl:param name="second" select="0"/>
    <xsl:param name="timezone" select="'Z'"/>
    <xsl:param name="pattern" select="''"/>
    <xsl:variable name="char" select="substring($pattern, 1, 1)"/>
    <xsl:choose>
      <xsl:when test="not($pattern)"/>
      <xsl:when test="$char = &quot;'&quot;">
        <xsl:choose>
          <xsl:when test="substring($pattern, 2, 1) = &quot;'&quot;">
            <xsl:text>'</xsl:text>
            <xsl:call-template name="date:_format-date">
              <xsl:with-param name="year" select="$year"/>
              <xsl:with-param name="month" select="$month"/>
              <xsl:with-param name="day" select="$day"/>
              <xsl:with-param name="hour" select="$hour"/>
              <xsl:with-param name="minute" select="$minute"/>
              <xsl:with-param name="second" select="$second"/>
              <xsl:with-param name="timezone" select="$timezone"/>
              <xsl:with-param name="pattern" select="substring($pattern, 3)"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:otherwise>
            <xsl:variable name="literal-value" select="substring-before(substring($pattern, 2), &quot;'&quot;)"/>
            <xsl:value-of select="$literal-value"/>
            <xsl:call-template name="date:_format-date">
              <xsl:with-param name="year" select="$year"/>
              <xsl:with-param name="month" select="$month"/>
              <xsl:with-param name="day" select="$day"/>
              <xsl:with-param name="hour" select="$hour"/>
              <xsl:with-param name="minute" select="$minute"/>
              <xsl:with-param name="second" select="$second"/>
              <xsl:with-param name="timezone" select="$timezone"/>
              <xsl:with-param name="pattern" select="substring($pattern, string-length($literal-value) + 2)"/>
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="not(contains('abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $char))">
        <xsl:value-of select="$char"/>
        <xsl:call-template name="date:_format-date">
          <xsl:with-param name="year" select="$year"/>
          <xsl:with-param name="month" select="$month"/>
          <xsl:with-param name="day" select="$day"/>
          <xsl:with-param name="hour" select="$hour"/>
          <xsl:with-param name="minute" select="$minute"/>
          <xsl:with-param name="second" select="$second"/>
          <xsl:with-param name="timezone" select="$timezone"/>
          <xsl:with-param name="pattern" select="substring($pattern, 2)"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="not(contains('GyMdhHmsSEDFwWakKz', $char))">
        <xsl:message>
            Invalid token in format string: <xsl:value-of select="$char"/>
        </xsl:message>
        <xsl:call-template name="date:_format-date">
          <xsl:with-param name="year" select="$year"/>
          <xsl:with-param name="month" select="$month"/>
          <xsl:with-param name="day" select="$day"/>
          <xsl:with-param name="hour" select="$hour"/>
          <xsl:with-param name="minute" select="$minute"/>
          <xsl:with-param name="second" select="$second"/>
          <xsl:with-param name="timezone" select="$timezone"/>
          <xsl:with-param name="pattern" select="substring($pattern, 2)"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="next-different-char" select="substring(translate($pattern, $char, ''), 1, 1)"/>
        <xsl:variable name="pattern-length">
          <xsl:choose>
            <xsl:when test="$next-different-char">
              <xsl:value-of select="string-length(substring-before($pattern, $next-different-char))"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="string-length($pattern)"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:choose>
          <xsl:when test="$char = 'G'">
            <xsl:choose>
              <xsl:when test="string($year) = 'NaN'"/>
              <xsl:when test="$year > 0">AD</xsl:when>
              <xsl:otherwise>BC</xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:when test="$char = 'M'">
            <xsl:choose>
              <xsl:when test="string($month) = 'NaN'"/>
              <xsl:when test="$pattern-length >= 3">
                <xsl:variable name="month-node" select="document('')/*/date:months/date:month[number($month)]"/>
                <xsl:choose>
                  <xsl:when test="$pattern-length >= 4">
                    <xsl:value-of select="$month-node"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="$month-node/@abbr"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$pattern-length = 2">
                <xsl:value-of select="format-number($month, '00')"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$month"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:when test="$char = 'E'">
            <xsl:choose>
              <xsl:when test="string($year) = 'NaN' or string($month) = 'NaN' or string($day) = 'NaN'"/>
              <xsl:otherwise>
                <xsl:variable name="month-days" select="sum(document('')/*/date:months/date:month[position() &lt; $month]/@length)"/>
                <xsl:variable name="days" select="$month-days + $day + boolean(((not(boolean($year mod 4)) and $year mod 100) or not(boolean($year mod 400))) and $month > 2)"/>
                <xsl:variable name="y-1" select="$year - 1"/>
                <xsl:variable name="dow" select="(($y-1 + floor($y-1 div 4) -                                              floor($y-1 div 100) + floor($y-1 div 400) +                                              $days)                                              mod 7) + 1"/>
                <xsl:variable name="day-node" select="document('')/*/date:days/date:day[number($dow)]"/>
                <xsl:choose>
                  <xsl:when test="$pattern-length >= 4">
                    <xsl:value-of select="$day-node"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="$day-node/@abbr"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:when test="$char = 'a'">
            <xsl:choose>
              <xsl:when test="string($hour) = 'NaN'"/>
              <xsl:when test="$hour >= 12">PM</xsl:when>
              <xsl:otherwise>AM</xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:when test="$char = 'z'">
            <xsl:choose>
              <xsl:when test="$timezone = 'Z'">UTC</xsl:when>
              <xsl:otherwise>UTC<xsl:value-of select="$timezone"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:variable name="padding">
              <xsl:choose>
                <xsl:when test="$pattern-length > 10">
                  <xsl:call-template name="str:padding">
                    <xsl:with-param name="length" select="$pattern-length"/>
                    <xsl:with-param name="chars" select="'0'"/>
                  </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="substring('0000000000', 1, $pattern-length)"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:variable>
            <xsl:choose>
              <xsl:when test="$char = 'y'">
                <xsl:choose>
                  <xsl:when test="string($year) = 'NaN'"/>
                  <xsl:when test="$pattern-length > 2">
                    <xsl:value-of select="format-number($year, $padding)"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number(substring($year, string-length($year) - 1), $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'd'">
                <xsl:choose>
                  <xsl:when test="string($day) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number($day, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'h'">
                <xsl:variable name="h" select="$hour mod 12"/>
                <xsl:choose>
                  <xsl:when test="string($hour) = 'NaN'"/>
                  <xsl:when test="$h">
                    <xsl:value-of select="format-number($h, $padding)"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number(12, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'H'">
                <xsl:choose>
                  <xsl:when test="string($hour) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number($hour, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'k'">
                <xsl:choose>
                  <xsl:when test="string($hour) = 'NaN'"/>
                  <xsl:when test="$hour">
                    <xsl:value-of select="format-number($hour, $padding)"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number(24, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'K'">
                <xsl:choose>
                  <xsl:when test="string($hour) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number($hour mod 12, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'm'">
                <xsl:choose>
                  <xsl:when test="string($minute) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number($minute, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 's'">
                <xsl:choose>
                  <xsl:when test="string($second) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number($second, $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'S'">
                <xsl:choose>
                  <xsl:when test="string($second) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="format-number(substring-after($second, '.'), $padding)"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="$char = 'F'">
                <xsl:choose>
                  <xsl:when test="string($day) = 'NaN'"/>
                  <xsl:otherwise>
                    <xsl:value-of select="floor($day div 7) + 1"/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="string($year) = 'NaN' or string($month) = 'NaN' or string($day) = 'NaN'"/>
              <xsl:otherwise>
                <xsl:variable name="month-days" select="sum(document('')/*/date:months/date:month[position() &lt; $month]/@length)"/>
                <xsl:variable name="days" select="$month-days + $day + boolean(((not($year mod 4) and $year mod 100) or not($year mod 400)) and $month > 2)"/>
                <xsl:choose>
                  <xsl:when test="$char = 'D'">
                    <xsl:value-of select="format-number($days, $padding)"/>
                  </xsl:when>
                  <xsl:when test="$char = 'w'">
                    <xsl:call-template name="date:_week-in-year">
                      <xsl:with-param name="days" select="$days"/>
                      <xsl:with-param name="year" select="$year"/>
                    </xsl:call-template>
                  </xsl:when>
                  <xsl:when test="$char = 'W'">
                    <xsl:variable name="y-1" select="$year - 1"/>
                    <xsl:variable name="day-of-week" select="(($y-1 + floor($y-1 div 4) - floor($y-1 div 100) + floor($y-1 div 400) +                                                   $days)                                                    mod 7) + 1"/>
                    <xsl:choose>
                      <xsl:when test="($day - $day-of-week) mod 7">
                        <xsl:value-of select="floor(($day - $day-of-week) div 7) + 2"/>
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:value-of select="floor(($day - $day-of-week) div 7) + 1"/>
                      </xsl:otherwise>
                    </xsl:choose>
                  </xsl:when>
                </xsl:choose>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:call-template name="date:_format-date">
          <xsl:with-param name="year" select="$year"/>
          <xsl:with-param name="month" select="$month"/>
          <xsl:with-param name="day" select="$day"/>
          <xsl:with-param name="hour" select="$hour"/>
          <xsl:with-param name="minute" select="$minute"/>
          <xsl:with-param name="second" select="$second"/>
          <xsl:with-param name="timezone" select="$timezone"/>
          <xsl:with-param name="pattern" select="substring($pattern, $pattern-length + 1)"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template name="date:_week-in-year">
    <xsl:param name="days"/>
    <xsl:param name="year"/>
    <xsl:variable name="y-1" select="$year - 1"/>
    <!-- this gives the day of the week, counting from Sunday = 0 -->
    <xsl:variable name="day-of-week" select="($y-1 + floor($y-1 div 4) - floor($y-1 div 100) + floor($y-1 div 400) +                           $days)                           mod 7"/>
    <!-- this gives the day of the week, counting from Monday = 1 -->
    <xsl:variable name="dow">
      <xsl:choose>
        <xsl:when test="$day-of-week">
          <xsl:value-of select="$day-of-week"/>
        </xsl:when>
        <xsl:otherwise>7</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="start-day" select="($days - $dow + 7) mod 7"/>
    <xsl:variable name="week-number" select="floor(($days - $dow + 7) div 7)"/>
    <xsl:choose>
      <xsl:when test="$start-day >= 4">
        <xsl:value-of select="$week-number + 1"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="not($week-number)">
            <xsl:call-template name="date:_week-in-year">
              <xsl:with-param name="days" select="365 + ((not($y-1 mod 4) and $y-1 mod 100) or not($y-1 mod 400))"/>
              <xsl:with-param name="year" select="$y-1"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$week-number"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- str.padding.template.xsl -->
  <!--  This is from the EXSLT.org Library (http://www.exslt.org/) -->
  <xsl:template name="str:padding">
    <xsl:param name="length" select="0"/>
    <xsl:param name="chars" select="' '"/>
    <xsl:choose>
      <xsl:when test="not($length) or not($chars)"/>
      <xsl:otherwise>
        <xsl:variable name="string" select="concat($chars, $chars, $chars, $chars, $chars,                                        $chars, $chars, $chars, $chars, $chars)"/>
        <xsl:choose>
          <xsl:when test="string-length($string) >= $length">
            <xsl:value-of select="substring($string, 1, $length)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="str:padding">
              <xsl:with-param name="length" select="$length"/>
              <xsl:with-param name="chars" select="$string"/>
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- actor.xsl -->
  <!-- Returns the name of the actor, if there is no name it returns the ActorObjectID that was passed in -->
  <xsl:template name="actorName">
    <xsl:param name="objID"/>
    <xsl:for-each select="/a:ContinuityOfCareRecord/a:Actors/a:Actor">
      <xsl:variable name="thisObjID" select="a:ActorObjectID"/>
      <xsl:if test="$objID = $thisObjID">
        <xsl:choose>
          <xsl:when test="a:Person">
            <xsl:choose>
              <xsl:when test="a:Person/a:Name/a:DisplayName">
                <xsl:value-of select="a:Person/a:Name/a:DisplayName"/>
              </xsl:when>
              <xsl:when test="a:Person/a:Name/a:CurrentName">
                <xsl:value-of select="a:Person/a:Name/a:CurrentName/a:Given"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:CurrentName/a:Middle"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:CurrentName/a:Family"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:CurrentName/a:Suffix"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:CurrentName/a:Title"/>
                <xsl:text xml:space="preserve"> </xsl:text>
              </xsl:when>
              <xsl:when test="a:Person/a:Name/a:BirthName">
                <xsl:value-of select="a:Person/a:Name/a:BirthName/a:Given"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:BirthName/a:Middle"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:BirthName/a:Family"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:BirthName/a:Suffix"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:value-of select="a:Person/a:Name/a:BirthName/a:Title"/>
                <xsl:text xml:space="preserve"> </xsl:text>
              </xsl:when>
              <xsl:when test="a:Person/a:Name/a:AdditionalName">
                <xsl:for-each select="a:Person/a:Name/a:AdditionalName">
                  <xsl:value-of select="a:Given"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Middle"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Family"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Suffix"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Title"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:if test="position() != last()">
                    <br/>
                  </xsl:if>
                </xsl:for-each>
              </xsl:when>
            </xsl:choose>
          </xsl:when>
          <xsl:when test="a:Organization">
            <xsl:value-of select="a:Organization/a:Name"/>
          </xsl:when>
          <xsl:when test="a:InformationSystem">
            <xsl:value-of select="a:InformationSystem/a:Name"/>
            <xsl:text xml:space="preserve"> </xsl:text>
            <xsl:if test="a:InformationSystem/a:Version">
              <xsl:value-of select="a:InformationSystem/a:Version"/>
              <xsl:text xml:space="preserve"> </xsl:text>
            </xsl:if>
            <xsl:if test="a:InformationSystem/a:Type">(<xsl:value-of select="a:InformationSystem/a:Type"/>)</xsl:if>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$objID"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>
  <!-- code.xsl -->
  <xsl:template match="a:Code">
    <xsl:value-of select="a:Value"/>
    <xsl:if test="a:CodingSystem">
      <xsl:text xml:space="preserve"> </xsl:text>(<xsl:value-of select="a:CodingSystem"/>)
		</xsl:if>
  </xsl:template>
  <!--datetime. xsl -->
  <!-- Displays the DateTime.  If ExactDateTime is present, it will format according
		 to the 'fmt' variable. The default format is: Oct 31, 2005 -->
  <xsl:template match="DateTime" name="dateTime">
    <xsl:param name="dt" select="."/>
    <xsl:param name="fmt">MMM dd, yyyy</xsl:param>
    <tr>
      <xsl:if test="$dt/a:Type/a:Text">
        <td>
          <xsl:value-of select="$dt/a:Type/a:Text"/>:</td>
      </xsl:if>
      <xsl:choose>
        <xsl:when test="$dt/a:ExactDateTime">
          <td>
            <xsl:call-template name="date:format-date">
              <xsl:with-param name="date-time">
                <xsl:value-of select="$dt/a:ExactDateTime"/>
              </xsl:with-param>
              <xsl:with-param name="pattern" select="$fmt"/>
            </xsl:call-template>
          </td>
        </xsl:when>
        <xsl:when test="$dt/a:Age">
          <td>
            <xsl:value-of select="$dt/a:Age/a:Value"/>
            <xsl:text xml:space="preserve"> </xsl:text>
            <xsl:value-of select="$dt/a:Age/a:Units/a:Unit"/>
          </td>
        </xsl:when>
        <xsl:when test="$dt/a:ApproximateDateTime">
          <td>
            <xsl:value-of select="$dt/a:ApproximateDateTime/a:Text"/>
          </td>
        </xsl:when>
        <xsl:when test="$dt/a:DateTimeRange">
          <td>
            <xsl:for-each select="$dt/a:DateTimeRange/a:BeginRange">
              <xsl:choose>
                <xsl:when test="$dt/a:ExactDateTime">
                  <xsl:call-template name="date:format-date">
                    <xsl:with-param name="date-time">
                      <xsl:value-of select="$dt/a:ExactDateTime"/>
                    </xsl:with-param>
                    <xsl:with-param name="pattern" select="$fmt"/>
                  </xsl:call-template>
                </xsl:when>
                <xsl:when test="$dt/a:Age">
                  <xsl:value-of select="$dt/a:Age/a:Value"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="$dt/a:Age/a:Units/a:Unit"/>
                </xsl:when>
                <xsl:when test="$dt/a:ApproximateDateTime">
                  <xsl:value-of select="$dt/a:ApproximateDateTime/a:Text"/>
                </xsl:when>
                <xsl:otherwise/>
              </xsl:choose>
            </xsl:for-each>
            <xsl:text xml:space="preserve"> </xsl:text>
            <xsl:text>-</xsl:text>
            <xsl:text xml:space="preserve"> </xsl:text>
            <xsl:for-each select="$dt/a:DateTimeRange/a:EndRange">
              <xsl:choose>
                <xsl:when test="$dt/a:ExactDateTime">
                  <xsl:call-template name="date:format-date">
                    <xsl:with-param name="date-time">
                      <xsl:value-of select="$dt/a:ExactDateTime"/>
                    </xsl:with-param>
                    <xsl:with-param name="pattern" select="$fmt"/>
                  </xsl:call-template>
                </xsl:when>
                <xsl:when test="$dt/a:Age">
                  <xsl:value-of select="$dt/a:Age/a:Value"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="$dt/a:Age/a:Units/a:Unit"/>
                </xsl:when>
                <xsl:when test="$dt/a:ApproximateDateTime">
                  <xsl:value-of select="$dt/a:ApproximateDateTime/a:Text"/>
                </xsl:when>
                <xsl:otherwise/>
              </xsl:choose>
            </xsl:for-each>
          </td>
        </xsl:when>
        <xsl:otherwise/>
      </xsl:choose>
    </tr>
  </xsl:template>
  <!-- defaultCSS.xsl -->
  <xsl:template name="defaultCCS">
    <style type="text/css">&lt;!--
*{
	font-size: small;
	font-family: Arial, sans-serif;
}
h1{
	font-size: 150%;
}
strong.clinical {
	color: #3300FF;
}
p {
	margin-left: 20px
}
span.header{
	font-weight: bold;
    font-size: medium;
    line-height: 16pt;
	padding-top: 10px;
}
table.list {
	padding-bottom: 5px;
	border: thin solid #cccccc;
	border-style-internal: thin solid #cccccc;
	BORDER-COLLAPSE: collapse;
	background: white;
	background-image: none
}
table.list th {
	text-align: left;
	FONT-WEIGHT: bold;
	COLOR: white;
	background: #006699;
	background-image: none
}
table.list td {
	padding: 5px;
	border: thin solid #cccccc;
	vertical-align: top;
}
table.internal {
	border: none;
}
table.internal td {
	vertical-align: top;
    padding: 1px;
    border: none;
}
table.internal tr.even{
	background: #CEFFFF;
	background-image: none
}
--&gt;</style>
  </xsl:template>
  <!-- directions.xsl -->
  <xsl:template match="a:Directions">
    <xsl:for-each select="a:Direction">
      <xsl:choose>
        <xsl:when test="position() mod 2=0">
          <tr class="even">
            <xsl:choose>
              <xsl:when test="a:Description/a:Text">
                <td>
                  <xsl:value-of select="a:Description/a:Text"/>
                </td>
              </xsl:when>
              <xsl:otherwise>
                <td>
				<xsl:value-of select="a:DeliveryMethod/a:Text"/>
				<xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Dose/a:Value"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Dose/a:Units/a:Unit"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Route/a:Text"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Frequency/a:Value"/>
                  <xsl:if test="a:Duration">
                    <xsl:text xml:space="preserve"> </xsl:text>(for <xsl:value-of select="a:Duration/a:Value"/>
                    <xsl:text xml:space="preserve"> </xsl:text>
                    <xsl:value-of select="a:Duration/a:Units/a:Unit"/>)
																								</xsl:if>
                </td>
                <xsl:if test="a:MultipleDirectionModifier/a:ObjectAttribute">
                  <td>
                  <xsl:if test="a:MultipleDirectionModifier/a:Text">
                   <xsl:value-of select="a:MultipleDirectionModifier/a:Text"/>
                   <xsl:text xml:space="preserve"> </xsl:text>
                   </xsl:if>
                    <xsl:for-each select="a:MultipleDirectionModifier/a:ObjectAttribute">
                      <xsl:value-of select="a:Attribute"/>
                      <br/>
                      <xsl:value-of select="a:AttributeValue/a:Value"/>
                    </xsl:for-each>
                  </td>
                </xsl:if>
              </xsl:otherwise>
            </xsl:choose>
          </tr>
        </xsl:when>
        <xsl:otherwise>
          <tr class="odd">
            <xsl:choose>
              <xsl:when test="a:Description/a:Text">
                <td>
                  <xsl:value-of select="a:Description/a:Text"/>
                </td>
              </xsl:when>
              <xsl:otherwise>
                <td>
                <xsl:value-of select="a:DeliveryMethod/a:Text"/>
				<xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Dose/a:Value"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Dose/a:Units/a:Unit"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Route/a:Text"/>
                  <xsl:text xml:space="preserve"> </xsl:text>
                  <xsl:value-of select="a:Frequency/a:Value"/>
                  <xsl:if test="a:Duration">
                    <xsl:text xml:space="preserve"> </xsl:text>(for <xsl:value-of select="a:Duration/a:Value"/>
                    <xsl:text xml:space="preserve"> </xsl:text>
                    <xsl:value-of select="a:Duration/a:Units/a:Unit"/>)
																								</xsl:if>
                </td>
                <xsl:if test="a:MultipleDirectionModifier/a:ObjectAttribute">
                  <td>
                    <xsl:for-each select="a:MultipleDirectionModifier/a:ObjectAttribute">
                      <xsl:value-of select="a:Attribute"/>
                      <br/>
                      <xsl:value-of select="a:AttributeValue/a:Value"/>
                    </xsl:for-each>
                  </td>
                </xsl:if>
              </xsl:otherwise>
            </xsl:choose>
          </tr>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>
  <!-- footer.xsl -->
  <!-- HTML Footer for CCR.XSL -->
  <xsl:template name="footer">
    <br/>
    <hr/>
    <table cellspacing="3">
      <tbody>
        <tr>
          <th>
            <font color="#CCCCCC" size="2">
<!--	The stylesheet used to generate this view of the CCR was provided by the American Academy of Family Physicians and the CCR Acceleration Task Force -->
<!--The stylesheet used to generate this view of the CCR was provided by Garden Health.-->
  The stylesheet used to generate this view of the CCR was provided by OEMR.
</font>
          </th>
        </tr>
        <tr>
          <td/>
        </tr>
        <tr>
          <td>
            <font color="#CCCCCC" size="3">
              <strong>Powered by the <a href="http://www.astm.org/cgi-bin/SoftCart.exe/DATABASE.CART/REDLINE_PAGES/E2369.htm?E+mystore" style="color:#CCCCCC;">ASTM E2369-05 Specification for the Continuity of Care Record (CCR)</a>
              </strong>
            </font>
          </td>
        </tr>
      </tbody>
    </table>
  </xsl:template>
  <!-- problemDescription.xsl -->
  <!-- Returns the description of the problem, if there is no name it returns the ObjectID that was passed in -->
  <xsl:template name="problemDescription">
    <xsl:param name="objID"/>
    <xsl:for-each select="/a:ContinuityOfCareRecord/a:Body/a:Problems/a:Problem">
      <xsl:variable name="thisObjID" select="a:CCRDataObjectID"/>
      <xsl:if test="$objID = $thisObjID">
        <xsl:choose>
          <xsl:when test="a:Description/a:Text">
            <xsl:value-of select="a:Description/a:Text"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$objID"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>
