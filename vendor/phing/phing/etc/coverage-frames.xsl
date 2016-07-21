<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
    xmlns:exsl="http://exslt.org/common"
    xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="exsl date">
<xsl:output method="html" indent="yes"/>
<xsl:decimal-format decimal-separator="." grouping-separator="," />
<!--
    Copyright  2001-2004 The Apache Software Foundation
   
     Licensed under the Apache License, Version 2.0 (the "License");
     you may not use this file except in compliance with the License.
     You may obtain a copy of the License at
   
         http://www.apache.org/licenses/LICENSE-2.0
   
     Unless required by applicable law or agreed to in writing, software
     distributed under the License is distributed on an "AS IS" BASIS,
     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
     See the License for the specific language governing permissions and
     limitations under the License.
   
-->

<!--

 Sample stylesheet to be used with Xdebug/Phing code coverage output.
 Based on JProbe stylesheets from Apache Ant.

 It creates a set of HTML files a la javadoc where you can browse easily
 through all packages and classes.

 @author Michiel Rook <a href="mailto:michiel.rook@gmail.com"/>
 @author Stephane Bailliez <a href="mailto:sbailliez@apache.org"/>

-->

<!-- default output directory is current directory -->
<xsl:param name="output.dir" select="'.'"/>
<xsl:param name="output.sorttable" select="'.'"/>
<xsl:param name="document.title" select="''"/>

<!-- ======================================================================
    Root element
    ======================================================================= -->
<xsl:template match="/snapshot">
    <!-- create the index.html -->
    <exsl:document href="efile://{$output.dir}/index.html">
        <xsl:call-template name="index.html"/>
    </exsl:document>

    <!-- create the stylesheet.css -->
    <exsl:document omit-xml-declaration="yes" href="efile://{$output.dir}/stylesheet.css">
        <xsl:call-template name="stylesheet.css"/>
    </exsl:document>

    <!-- create the overview-packages.html at the root -->
    <exsl:document href="efile://{$output.dir}/overview-summary.html">
        <xsl:apply-templates select="." mode="overview.packages"/>
    </exsl:document>

    <!-- create the all-packages.html at the root -->
    <exsl:document href="efile://{$output.dir}/overview-frame.html">
        <xsl:apply-templates select="." mode="all.packages"/>
    </exsl:document>

    <!-- create the all-classes.html at the root -->
    <exsl:document href="efile://{$output.dir}/allclasses-frame.html">
        <xsl:apply-templates select="." mode="all.classes"/>
    </exsl:document>

    <!-- process all packages -->
    <xsl:apply-templates select="./package" mode="write"/>

    <!-- process all subpackages -->
    <xsl:apply-templates select="./package/subpackage" mode="write"/>
</xsl:template>

<!-- =======================================================================
    Frameset definition. Entry point for the report.
    3 frames: packageListFrame, classListFrame, classFrame
    ======================================================================= -->
<xsl:template name="index.html">
<html>
    <head><title><xsl:value-of select="$document.title"/> Coverage Results</title></head>
    <frameset cols="20%,80%">
        <frameset rows="30%,70%">
            <frame src="overview-frame.html" name="packageListFrame"/>
            <frame src="allclasses-frame.html" name="classListFrame"/>
        </frameset>
        <frame src="overview-summary.html" name="classFrame"/>
    </frameset>
    <noframes>
        <h2>Frame Alert</h2>
        <p>
        This document is designed to be viewed using the frames feature. If you see this message, you are using a non-frame-capable web client.
        </p>
    </noframes>
</html>
</xsl:template>

<!-- =======================================================================
    Stylesheet CSS used
    ======================================================================= -->
<!-- this is the stylesheet css to use for nearly everything -->
<xsl:template name="stylesheet.css">
	<xsl:if test="$output.sorttable = 1">
	.sortable th {
	    cursor: pointer;
	}
	</xsl:if>
    .bannercell {
      border: 0px;
      padding: 0px;
    }
    body {
      margin-left: 10;
      margin-right: 10;
      background-color:#FFFFFF;
      font-family: verdana,arial,sanserif;
      color:#000000;
    }
    a {
      color: #003399;
    }
    a:hover {
      color: #888888;
    }
    .a td {
      background: #efefef;
    }
    .b td {
      background: #fff;
    }
    th, td {
      text-align: left;
      vertical-align: top;
    }
    th {
      font-weight:bold;
      background: #ccc;
      color: black;
    }
    table, th, td {
      font-size: 12px;
      border: none
    }
    table.log tr td, tr th {
    }
    h2 {
      font-weight:bold;
      font-size: 12px;
      margin-bottom: 5;
    }
    h3 {
      font-size:100%;
      font-weight: 12px;
       background: #DFDFDF
      color: white;
      text-decoration: none;
      padding: 5px;
      margin-right: 2px;
      margin-left: 2px;
      margin-bottom: 0;
    }
    .small {
       font-size: 9px;
    }
    td.legendItem {
      font-weight: bold;
      padding-bottom: 2px;
      padding-right: 6px;
      padding-top: 6px;
      text-align: right;
    }
    td.legendValue {
      color: #2E3436;
      font-weight: bold;
      padding-bottom: 2px;
      padding-top: 6px;
      text-align: left;
    }
    span.LegendCovered {
      background-color: #8AE234;
      margin-right: 2px;
      padding-left: 10px;
      padding-right: 10px;
      text-align: center;
    }
    span.LegendUncovered {
      background-color: #F0C8C8;
      margin-right: 2px;
      padding-left: 10px;
      padding-right: 10px;
      text-align: center;
    }
    span.LegendDeadCode {
      background-color: #D3D7CF;
      margin-right: 2px;
      padding-left: 10px;
      padding-right: 10px;
      text-align: center;
    }
TD.empty {
    FONT-SIZE: 2px; BACKGROUND: #c0c0c0; BORDER:#9c9c9c 1px solid;
    color: #c0c0c0;
}
TD.fullcover {
    FONT-SIZE: 2px; BACKGROUND: #00df00; BORDER:#9c9c9c 1px solid;
    color: #00df00;
}
TD.covered {
    FONT-SIZE: 2px; BACKGROUND: #00df00; BORDER-LEFT:#9c9c9c 1px solid;BORDER-TOP:#9c9c9c 1px solid;BORDER-BOTTOM:#9c9c9c 1px solid;
    color: #00df00;
}
TD.uncovered {
    FONT-SIZE: 2px; BACKGROUND: #df0000; BORDER:#9c9c9c 1px solid;
    color: #df0000;
}
PRE.srcLine {
  BACKGROUND: #ffffff; MARGIN-TOP: 0px; MARGIN-BOTTOM: 0px; 
}
    PRE.srcLineUncovered {
  BACKGROUND: #F0C8C8; MARGIN-TOP: 0px; MARGIN-BOTTOM: 0px; 
}
    PRE.srcLineCovered {
      BACKGROUND: #8AE234; MARGIN-TOP: 0px; MARGIN-BOTTOM: 0px;
    }
    PRE.srcLineDeadCode {
      BACKGROUND: #D3D7CF; MARGIN-TOP: 0px; MARGIN-BOTTOM: 0px;
    }
td.lineCount, td.coverageCount {
      BACKGROUND: #F0F0F0; PADDING-RIGHT: 3px;
      text-align: right;
}
    td.lineCountCovered, td.coverageCountCovered {
          background: #8AE234; PADDING-RIGHT: 3px;
      text-align: right;
}
    td.srcLineCovered {
          background: #8AE234;
    }
    td.lineCountUncovered, td.coverageCountUncovered {
      background: #F0C8C8; PADDING-RIGHT: 3px;
      text-align: right;
}
    td.srcLineUncovered {
      background: #F0C8C8;
}
    td.lineCountDeadCode, td.coverageCountDeadCode {
          background: #D3D7CF; PADDING-RIGHT: 3px;
          text-align: right;
    }
    td.srcLineDeadCode {
          background: #D3D7CF;
    }
td.srcLine {
      background: #C8C8F0;
}
TD.srcLineClassStart {
   WIDTH: 100%; BORDER-TOP:#dcdcdc 1px solid; FONT-WEIGHT: bold;    
}
.srcLine , .srcLine ol, .srcLine ol li {margin: 0;}
.srcLine .de1, .srcLine .de2 {font-family: 'Courier New', Courier, monospace; font-weight: normal;}
.srcLine .imp {font-weight: bold; color: red;}
.srcLine .kw1 {color: #b1b100;}
.srcLine .kw2 {color: #000000; font-weight: bold;}
.srcLine .kw3 {color: #000066;}
.srcLine .co1 {color: #808080; font-style: italic;}
.srcLine .co2 {color: #808080; font-style: italic;}
.srcLine .coMULTI {color: #808080; font-style: italic;}
.srcLine .es0 {color: #000099; font-weight: bold;}
.srcLine .br0 {color: #66cc66;}
.srcLine .st0 {color: #ff0000;}
.srcLine .nu0 {color: #cc66cc;}
.srcLine .me1 {color: #006600;}
.srcLine .me2 {color: #006600;}
.srcLine .re0 {color: #0000ff;}
</xsl:template>

<!-- =======================================================================
    List of all classes in all packages
    This will be the first page in the classListFrame
    ======================================================================= -->
<xsl:template match="snapshot" mode="all.classes">
    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link"/>
        </head>
        <body>
            <h2>All Classes</h2>
            <table width="100%">
                <xsl:for-each select="package/class">
                    <xsl:sort select="@name"/>
                    <xsl:variable name="package.name" select="(ancestor::package)[last()]/@name"/>
                    <xsl:variable name="link">
                        <xsl:if test="not($package.name='')">
                            <xsl:value-of select="translate($package.name,'._\','///')"/><xsl:text>/</xsl:text>
                        </xsl:if><xsl:value-of select="@name"/><xsl:text>.html</xsl:text>
                    </xsl:variable>
                    <tr>
                        <td nowrap="nowrap">
                            <a target="classFrame" href="{$link}">
                                <xsl:if test="not($package.name='')">
                                    <xsl:value-of select="$package.name"/><xsl:text>\</xsl:text>
                                </xsl:if><xsl:value-of select="@name"/>
                            </a>
                            <xsl:choose>
								<xsl:when test="@totalcount=0">
									<i> (-)</i>
								</xsl:when>
								<xsl:otherwise>
									<i> (<xsl:value-of select="format-number(@totalcovered div @totalcount, '0.0%')"/>)</i>
								</xsl:otherwise>
							</xsl:choose>
                        </td>
                    </tr>
                </xsl:for-each>
                <xsl:for-each select="package/subpackage/class">
                    <xsl:sort select="@name"/>
                    <xsl:variable name="package.name" select="(ancestor::package)[last()]/@name"/>
                    <xsl:variable name="subpackage.name" select="(ancestor::subpackage)[last()]/@name"/>
                    <xsl:variable name="link">
                        <xsl:if test="not($package.name='')">
                            <xsl:value-of select="translate($package.name,'._\','///')"/><xsl:text>/</xsl:text>
                        </xsl:if>
                        <xsl:if test="not($subpackage.name='')">
                            <xsl:value-of select="translate($subpackage.name,'._\','///')"/><xsl:text>/</xsl:text>
                        </xsl:if>
                        <xsl:value-of select="@name"/><xsl:text>.html</xsl:text>
                    </xsl:variable>
                    <tr>
                        <td nowrap="nowrap">
                            <a target="classFrame" href="{$link}"><xsl:value-of select="@name"/></a>
                            <xsl:choose>
                                <xsl:when test="@totalcount=0">
                                    <i> (-)</i>
                                </xsl:when>
                                <xsl:otherwise>
                                    <i> (<xsl:value-of select="format-number(@totalcovered div @totalcount, '0.0%')"/>)</i>
                                </xsl:otherwise>
                            </xsl:choose>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </body>
    </html>
</xsl:template>

<!-- list of all packages -->
<xsl:template match="snapshot" mode="all.packages">
    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link"/>
        </head>
        <body>
            <h2><a href="overview-summary.html" target="classFrame">Overview</a></h2>
            <h2>All Packages</h2>
            <table width="100%">
                <xsl:for-each select="package">
                    <xsl:sort select="@name" order="ascending"/>
                    <tr>
                        <td nowrap="nowrap">
                            <a href="{translate(@name,'._\','///')}/package-summary.html" target="classFrame">
                                <xsl:value-of select="@name"/>
                            </a>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </body>
    </html>
</xsl:template>

<!-- overview of statistics in packages -->
<xsl:template match="snapshot" mode="overview.packages">
    <html>
        <head>
            <title>Coverage Results Overview</title>
            <xsl:if test="$output.sorttable = 1">
                <script language="JavaScript" src="http://www.phing.info/support/sorttable.js"/>
            </xsl:if>
            <xsl:call-template name="create.stylesheet.link"/>
        </head>
        <body onload="open('allclasses-frame.html','classListFrame')">
        <xsl:call-template name="pageHeader"/>
        <table class="log" cellpadding="5" cellspacing="0" width="100%">
            <tr class="a">
                <td class="small">Packages: <xsl:value-of select="count(package)"/></td>
                <td class="small">Subpackages: <xsl:value-of select="count(package/subpackage)"/></td>
                <td class="small">Classes: <xsl:value-of select="count(package/class) + count(package/subpackage/class)"/></td>
                <td class="small">Methods: <xsl:value-of select="@methodcount"/></td>
                <td class="small">LOC: <xsl:value-of select="count(package/class/sourcefile/sourceline) + count(package/subpackage/class/sourcefile/sourceline)"/></td>
                <td class="small">Statements: <xsl:value-of select="@statementcount"/></td>
            </tr>
        </table>
        <br/>

        <table class="log" cellpadding="5" cellspacing="0" width="100%">
            <tr>
                <th width="100%" nowrap="nowrap"></th>
                <th>Statements</th>
                <th>Methods</th>
                <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
            </tr>
            <tr class="a">
        	<td><b>Project <xsl:value-of select="$document.title"/></b></td>
                <xsl:call-template name="stats.formatted"/>
            </tr>
            <tr><td colspan="4"><br/></td></tr>
        </table>
        
        <table class="log sortable" cellpadding="5" cellspacing="0" width="100%">
            <tr>
                <th width="100%">Packages</th>
                <th>Statements</th>
                <th>Methods</th>
                <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
            </tr>
            <!-- display packages and sort them via their coverage rate -->
            <xsl:for-each select="package">
                <xsl:sort data-type="number" select="@totalcovered div @totalcount"/>
                <tr>
                  <xsl:call-template name="alternate-row"/>
                    <td><a href="{translate(@name,'._\','///')}/package-summary.html"><xsl:value-of select="@name"/></a></td>
                    <xsl:call-template name="stats.formatted"/>
                </tr>
            </xsl:for-each>
        </table>
        <xsl:call-template name="pageFooter"/>
        </body>
        </html>
</xsl:template>

<!--
 detailed info for a package. It will output the list of classes
, the summary page, and the info for each class
-->
<xsl:template match="package" mode="write">
    <xsl:variable name="package.dir">
        <xsl:if test="not(@name = '')"><xsl:value-of select="translate(@name,'._\','///')"/></xsl:if>
        <xsl:if test="@name = ''">.</xsl:if>
    </xsl:variable>

    <!-- create a classes-list.html in the package directory -->
    <exsl:document href="efile://{$output.dir}/{$package.dir}/package-frame.html">
        <xsl:apply-templates select="." mode="classes.list"/>
    </exsl:document>

    <!-- create a package-summary.html in the package directory -->
    <exsl:document href="efile://{$output.dir}/{$package.dir}/package-summary.html">
        <xsl:apply-templates select="." mode="package.summary"/>
    </exsl:document>

    <!-- for each class in package, creates a @name.html -->
    <xsl:for-each select="./class">
        <exsl:document href="efile://{$output.dir}/{$package.dir}/{@name}.html">
            <xsl:apply-templates select="." mode="class.details"/>
        </exsl:document>
    </xsl:for-each>

    <!-- for each class in subpackage, creates a @name.html -->
    <xsl:for-each select="subpackage">
        <xsl:variable name="subpackage.dir">
            <xsl:if test="not(@name = '')"><xsl:value-of select="translate(@name,'._\','///')"/></xsl:if>
            <xsl:if test="@name = ''">.</xsl:if>
        </xsl:variable>
        <xsl:for-each select="./class">
            <exsl:document href="efile://{$output.dir}/{$package.dir}/{$subpackage.dir}/{@name}.html">
                <xsl:apply-templates select="." mode="class.details"/>
            </exsl:document>
        </xsl:for-each>
    </xsl:for-each>
</xsl:template>

<!--
 detailed info for a subpackage. It will output the list of classes and the summary page
-->
<xsl:template match="subpackage" mode="write">
    <xsl:variable name="package.name" select="(ancestor::package)[last()]/@name"/>

    <xsl:variable name="package.dir">
        <xsl:if test="not($package.name = '')"><xsl:value-of select="translate($package.name,'._\','///')"/></xsl:if>
        <xsl:if test="$package.name = ''">.</xsl:if>
    </xsl:variable>

    <xsl:variable name="subpackage.dir">
        <xsl:if test="not(@name = '')"><xsl:value-of select="translate(@name,'._\','///')"/></xsl:if>
        <xsl:if test="@name = ''">.</xsl:if>
    </xsl:variable>

    <!-- create a classes-list.html in the subpackage directory -->
    <exsl:document href="efile://{$output.dir}/{$package.dir}/{$subpackage.dir}/subpackage-frame.html">
        <xsl:apply-templates select="." mode="classes.list"/>
    </exsl:document>

    <!-- create a subpackage-summary.html in the subpackage directory -->
    <exsl:document href="efile://{$output.dir}/{$package.dir}/{$subpackage.dir}/subpackage-summary.html">
        <xsl:apply-templates select="." mode="subpackage.summary"/>
    </exsl:document>
</xsl:template>

<!-- list of classes in a package -->
<xsl:template match="package" mode="classes.list">
    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name" select="@name"/>
            </xsl:call-template>
        </head>
        <body>
            <table width="100%">
                <tr>
                    <td nowrap="nowrap">
                        <H2><a href="package-summary.html" target="classFrame"><xsl:value-of select="@name"/></a></H2>
                    </td>
                </tr>
            </table>

            <h2>Subpackages</h2>
            <table width="100%">
                <xsl:for-each select="subpackage">
                    <xsl:sort select="@name"/>
                    <tr>
                        <td nowrap="nowrap">
                            <a href="{translate(@name,'._\','///')}/subpackage-summary.html" target="classFrame"><xsl:value-of select="@name"/></a>
                            <xsl:choose>
                                <xsl:when test="@totalcount=0">
                                    <i> (-)</i>
                                </xsl:when>
                                <xsl:otherwise>
                                    <i> (<xsl:value-of select="format-number(@totalcovered div @totalcount, '0.0%')"/>)</i>
                                </xsl:otherwise>
                            </xsl:choose>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>

            <h2>Classes</h2>
            <table width="100%">
                <xsl:for-each select="class">
                    <xsl:sort select="@name"/>
                    <tr>
                        <td nowrap="nowrap">
                            <a href="{@name}.html" target="classFrame"><xsl:value-of select="@name"/></a>
                            <xsl:choose>
								<xsl:when test="@totalcount=0">
									<i> (-)</i>
								</xsl:when>
								<xsl:otherwise>
                            		<i>(<xsl:value-of select="format-number(@totalcovered div @totalcount, '0.0%')"/>)</i>
                            	</xsl:otherwise>
                            </xsl:choose>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </body>
    </html>
</xsl:template>

<!-- list of classes in a subpackage -->
<xsl:template match="subpackage" mode="classes.list">
    <xsl:variable name="fullpackage.name">
        <xsl:value-of select="(ancestor::package)[last()]/@name"/>
        <!-- append subpackage name if exists -->
        <xsl:if test="not(@name='')">
            <xsl:choose>
                <!-- determine path separator -->
                <xsl:when test="contains((ancestor::package)[last()]/@name, '_') or contains(@name, '_')">
                    <xsl:text>_</xsl:text>
                </xsl:when>
                <xsl:when test="contains((ancestor::package)[last()]/@name, '\') or contains(@name, '\')">
                    <xsl:text>\</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>.</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:value-of select="@name"/>
        </xsl:if>
    </xsl:variable>

    <xsl:variable name="package.name" select="(ancestor::package)[last()]/@name"/>

    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name" select="$fullpackage.name"/>
            </xsl:call-template>
        </head>
        <body>
            <table width="100%">
                <tr>
                    <td nowrap="nowrap">
                        <H2>
                            <xsl:call-template name="create.package-summary.link">
                                <xsl:with-param name="package.name" select="$package.name"/>
                                <xsl:with-param name="fullpackage.name" select="$fullpackage.name"/>
                            </xsl:call-template>::<a href="subpackage-summary.html" target="classFrame"><xsl:value-of select="@name"/></a>
                        </H2>
                    </td>
                </tr>
            </table>

            <h2>Classes</h2>
            <table width="100%">
                <xsl:for-each select="class">
                    <xsl:sort select="@name"/>
                    <tr>
                        <td nowrap="nowrap">
                            <a href="{@name}.html" target="classFrame"><xsl:value-of select="@name"/></a>
                            <xsl:choose>
                                <xsl:when test="@totalcount=0">
                                    <i> (-)</i>
                                </xsl:when>
                                <xsl:otherwise>
                                    <i> (<xsl:value-of select="format-number(@totalcovered div @totalcount, '0.0%')"/>)</i>
                                </xsl:otherwise>
                            </xsl:choose>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </body>
    </html>
</xsl:template>

<!-- summary of a package -->
<xsl:template match="package" mode="package.summary">
    <html>
        <head>
            <title>Coverage Results for <xsl:value-of select="@name"/></title>
            <xsl:if test="$output.sorttable = 1">
                <script language="JavaScript" src="http://www.phing.info/support/sorttable.js"/>
            </xsl:if>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name" select="@name"/>
            </xsl:call-template>
        </head>
        <!-- when loading this package, it will open the classes into the frame -->
        <body onload="open('package-frame.html','classListFrame')">
            <xsl:call-template name="pageHeader"/>
            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr class="a">
                    <td class="small">Subpackages: <xsl:value-of select="count(subpackage)"/></td>
                    <td class="small">Classes: <xsl:value-of select="count(class) + count(subpackage/class)"/></td>
                    <td class="small">Methods: <xsl:value-of select="@methodcount"/></td>
                    <td class="small">LOC: <xsl:value-of select="count(class/sourcefile/sourceline) + count(subpackage/class/sourcefile/sourceline)"/></td>
                    <td class="small">Statements: <xsl:value-of select="@statementcount"/></td>
                </tr>
            </table>        
            <br/>

            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr>
                    <th width="100%">Package</th>
                    <th>Statements</th>
                    <th>Methods</th>
                    <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
                </tr>
                <xsl:apply-templates select="." mode="stats"/>
                <tr>
                    <td colspan="5"><br/></td>
                </tr>
            </table>

            <xsl:if test="count(subpackage) &gt; 0">
            <table class="log sortable" cellpadding="5" cellspacing="0" width="100%">
                <tr>
                    <th width="100%">Subpackages</th>
                    <th>Statements</th>
                    <th>Methods</th>
                    <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
                </tr>
                <xsl:apply-templates select="subpackage" mode="stats">
                    <xsl:sort data-type="number" select="@totalcovered div @totalcount"/>
                </xsl:apply-templates>
                <tr>
                    <td colspan="5"><br/></td>
                </tr>
            </table>
            </xsl:if>

            <xsl:if test="count(class) &gt; 0">
            <table class="log sortable" cellpadding="5" cellspacing="0" width="100%">
                <tr>
                    <th width="100%">Classes</th>
                    <th>Statements</th>
                    <th>Methods</th>
                    <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
                </tr>
                <xsl:apply-templates select="class" mode="stats">
                    <xsl:sort data-type="number" select="@totalcovered div @totalcount"/>
                </xsl:apply-templates>
            </table>
            </xsl:if>
            <xsl:call-template name="pageFooter"/>
        </body>
    </html>
</xsl:template>

<!-- summary of a subpackage -->
<xsl:template match="subpackage" mode="subpackage.summary">
    <xsl:variable name="fullpackage.name">
        <xsl:value-of select="(ancestor::package)[last()]/@name"/>
        <!-- append subpackage name if exists -->
        <xsl:if test="not(@name='')">
            <xsl:choose>
                <!-- determine path separator -->
                <xsl:when test="contains((ancestor::package)[last()]/@name, '_') or contains(@name, '_')">
                    <xsl:text>_</xsl:text>
                </xsl:when>
                <xsl:when test="contains((ancestor::package)[last()]/@name, '\') or contains(@name, '\')">
                    <xsl:text>\</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>.</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:value-of select="@name"/>
        </xsl:if>
    </xsl:variable>

    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name" select="$fullpackage.name"/>
            </xsl:call-template>
        </head>
        <!-- when loading this subpackage, it will open the classes into the frame -->
        <body onload="open('subpackage-frame.html','classListFrame')">
            <xsl:call-template name="pageHeader"/>
            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr class="a">
                    <td class="small">Classes: <xsl:value-of select="count(class)"/></td>
                    <td class="small">Methods: <xsl:value-of select="@methodcount"/></td>
                    <td class="small">LOC: <xsl:value-of select="count(class/sourcefile/sourceline)"/></td>
                    <td class="small">Statements: <xsl:value-of select="@statementcount"/></td>
                </tr>
            </table>
            <br/>

            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr>
                    <th width="100%">Subpackage</th>
                    <th>Statements</th>
                    <th>Methods</th>
                    <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
                </tr>
                <xsl:apply-templates select="." mode="stats.summary"/>

                <xsl:if test="count(class) &gt; 0">
                    <tr>
                        <td colspan="3"><br/></td>
                    </tr>
                    <tr>
                        <th width="100%">Classes</th>
                        <th>Statements</th>
                        <th>Methods</th>
                        <th width="350" colspan="2" nowrap="nowrap">Total coverage</th>
                    </tr>
                    <xsl:apply-templates select="class" mode="stats">
                        <xsl:sort data-type="number" select="@totalcovered div @totalcount"/>
                    </xsl:apply-templates>
                </xsl:if>
            </table>
            <xsl:call-template name="pageFooter"/>
        </body>
    </html>
</xsl:template>

<!-- details of a class -->
<xsl:template match="class" mode="class.details">
    <xsl:variable name="subpackage.name" select="(ancestor::subpackage)[last()]/@name"/>

    <xsl:variable name="fullpackage.name">
        <xsl:value-of select="(ancestor::package)[last()]/@name"/>
        <!-- append subpackage name if exists -->
        <xsl:if test="not($subpackage.name='')">
            <xsl:choose>
                <!-- determine path/package separator -->
                <xsl:when test="contains((ancestor::package)[last()]/@name, '_') or contains($subpackage.name, '_')">
                    <xsl:text>_</xsl:text>
                </xsl:when>
                <xsl:when test="contains((ancestor::package)[last()]/@name, '\') or contains($subpackage.name, '\')">
                    <xsl:text>\</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>.</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:value-of select="$subpackage.name"/>
        </xsl:if>
    </xsl:variable>

    <html>
        <head>
            <xsl:call-template name="create.stylesheet.link">
                <xsl:with-param name="package.name" select="$fullpackage.name"/>
            </xsl:call-template>
        </head>
        <body>
            <xsl:call-template name="pageHeader"/>
            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr class="a">
                    <td class="small">Methods: <xsl:value-of select="@methodcount"/></td>
                    <td class="small">LOC: <xsl:value-of select="count(sourcefile/sourceline)"/></td>
                    <td class="small">Statements: <xsl:value-of select="@statementcount"/></td>
                </tr>
            </table>        

            <!-- legend -->
            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td class="legendItem" width="0%">Legend:</td>
                        <td class="legendValue" width="100%">
                            <span class="legendCovered">executed</span>
                            <span class="legendUncovered">not executed</span>
                            <span class="legendDeadCode">dead code</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- class summary -->
            <table class="log" cellpadding="5" cellspacing="0" width="100%">
                <tr>
                    <th width="100%">Source file</th>
                    <th>Statements</th>
                    <th>Methods</th>
                    <th width="250" colspan="2" nowrap="nowrap">Total coverage</th>
                </tr>
                <tr>
                    <xsl:call-template name="alternate-row"/>
                    <td><xsl:value-of select="sourcefile/@name"/></td>
                    <xsl:call-template name="stats.formatted"/>
                </tr>
            </table>
            <table cellspacing="0" cellpadding="0" width="100%">
                <xsl:apply-templates select="sourcefile/sourceline"/>
            </table>
            <br/>
            <xsl:call-template name="pageFooter"/>
        </body>
    </html>
</xsl:template>

<!-- Page Header -->
<xsl:template name="pageHeader">
  <!-- jakarta logo -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="bannercell" rowspan="2">
      <a href="http://www.phing.info/">
      <img src="http://www.phing.info/images/phing.gif" alt="http://www.phing.info/" align="left" border="0"/>
      </a>
    </td>
        <td style="text-align:right"><h2>Source Code Coverage</h2></td>
        </tr>
        <tr>
        <td style="text-align:right">Designed for use with <a href='http://www.phpunit.de'>PHPUnit</a>, <a href='http://www.xdebug.org/'>Xdebug</a> and <a href='http://www.phing.info/'>Phing</a>.</td>
        </tr>
  </table>
    <hr size="1"/>
</xsl:template>

<!-- Page Footer -->
<xsl:template name="pageFooter">
    <table width="100%">
      <tr><td><hr noshade="yes" size="1"/></td></tr>
      <tr><td class="small">Report generated at <xsl:value-of select="date:date-time()"/></td></tr>
    </table>
</xsl:template>

<xsl:template match="package" mode="stats">
    <tr>
      <xsl:call-template name="alternate-row"/>
        <td><xsl:value-of select="@name"/></td>
        <xsl:call-template name="stats.formatted"/>
    </tr>
</xsl:template>

<xsl:template match="subpackage" mode="stats">
    <tr>
        <xsl:call-template name="alternate-row"/>
        <td><a href="{translate(@name,'._\','///')}/subpackage-summary.html" target="classFrame"><xsl:value-of select="@name"/></a></td>
        <xsl:call-template name="stats.formatted"/>
    </tr>
</xsl:template>

<xsl:template match="subpackage" mode="stats.summary">
    <tr>
        <xsl:call-template name="alternate-row"/>
        <td><xsl:value-of select="@name"/></td>
        <xsl:call-template name="stats.formatted"/>
    </tr>
</xsl:template>

<xsl:template match="class" mode="stats">
    <tr>
      <xsl:call-template name="alternate-row"/>
        <td><a href="{@name}.html" target="classFrame"><xsl:value-of select="@name"/></a></td>
        <xsl:call-template name="stats.formatted"/>
    </tr>
</xsl:template>

<xsl:template name="stats.formatted">
    <xsl:choose>
        <xsl:when test="@statementcount=0">
            <td>-</td>
        </xsl:when>
        <xsl:otherwise>
            <td>
            <xsl:value-of select="format-number(@statementscovered div @statementcount,'0.0%')"/>
            </td>
        </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
        <xsl:when test="@methodcount=0">
            <td>-</td>
        </xsl:when>
        <xsl:otherwise>
            <td>
            <xsl:value-of select="format-number(@methodscovered div @methodcount,'0.0%')"/>
            </td>
        </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
        <xsl:when test="@totalcount=0">
            <td>-</td>
            <td>
            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="display: inline">
                <tr>
                    <td class="empty" width="200" height="12">&#160;</td>
                </tr>
            </table>
            </td>
        </xsl:when>
        <xsl:otherwise>
            <td>
            <xsl:value-of select="format-number(@totalcovered div @totalcount,'0.0%')"/>
            </td>
            <td>
            <xsl:variable name="leftwidth"><xsl:value-of select="format-number((@totalcovered * 200) div @totalcount,'0')"/></xsl:variable>
            <xsl:variable name="rightwidth"><xsl:value-of select="format-number(200 - (@totalcovered * 200) div @totalcount,'0')"/></xsl:variable>
            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="display: inline">
                <tr>
                    <xsl:choose>
                        <xsl:when test="$leftwidth=200">
                            <td class="fullcover" width="200" height="12">&#160;</td>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:if test="not($leftwidth=0)">
                                <td class="covered" width="{$leftwidth}" height="12">&#160;</td>
                            </xsl:if>
                            <xsl:if test="not($rightwidth=0)">
                                <td class="uncovered" width="{$rightwidth}" height="12">&#160;</td>
                            </xsl:if>
                        </xsl:otherwise>
                    </xsl:choose>
                </tr>
            </table>
            </td>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="sourceline">
    <tr>
        <xsl:if test="@coveredcount>0">
            <td class="lineCountCovered"><xsl:value-of select="position()"/></td>
            <td class="lineCountCovered"><xsl:value-of select="@coveredcount"/></td>
        </xsl:if>
        <xsl:if test="@coveredcount=-1">
            <td class="lineCountUncovered"><xsl:value-of select="position()"/></td>
            <td class="coverageCountUncovered"></td>
        </xsl:if>
        <xsl:if test="@coveredcount=-2">
            <td class="lineCountDeadCode"><xsl:value-of select="position()"/></td>
            <td class="coverageCountDeadCode"></td>
        </xsl:if>
        <xsl:if test="@coveredcount=0">
            <td class="lineCount"><xsl:value-of select="position()"/></td>
            <td class="coverageCount"></td>
        </xsl:if>

        <xsl:if test="@coveredcount>0">
            <td class="srcLineCovered">
            <xsl:if test="@startclass=1">
            	<xsl:attribute name="class">srcLineClassStart</xsl:attribute>
            </xsl:if>
                <pre class="srcLineCovered"><xsl:value-of select="."/></pre>
            </td>
            </xsl:if>
        <xsl:if test="@coveredcount=-1">
            <td class="srcLineUncovered">
                <xsl:if test="@startclass=1">
                    <xsl:attribute name="class">srcLineClassStart</xsl:attribute>
            </xsl:if>
                <pre class="srcLineUncovered"><xsl:value-of select="."/></pre>
            </td>
        </xsl:if>
        <xsl:if test="@coveredcount=-2">
            <td class="srcLineDeadCode">
                <xsl:if test="@startclass=1">
                    <xsl:attribute name="class">srcLineClassStart</xsl:attribute>
                </xsl:if>
                <pre class="srcLineDeadCode"><xsl:value-of select="."/></pre>
            </td>
        </xsl:if>
            <xsl:if test="@coveredcount=0">
            <td>
                <xsl:if test="@startclass=1">
                    <xsl:attribute name="class">srcLineClassStart</xsl:attribute>
                </xsl:if>
                <pre class="srcLine"><xsl:value-of select="."/></pre>
            </td>
            </xsl:if>
    </tr>
</xsl:template>

<!--
    transform string like a.b.c to ../../../
    transform string like a_b_c to ../../../
    @param path the path to transform into a descending directory path
-->
<xsl:template name="path">
    <xsl:param name="path"/>
    <xsl:if test="contains($path,'\')">
        <xsl:text>../</xsl:text>
        <xsl:call-template name="path">
            <xsl:with-param name="path"><xsl:value-of select="substring-after($path,'\')"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
    <xsl:if test="contains($path,'.')">
        <xsl:text>../</xsl:text>
        <xsl:call-template name="path">
            <xsl:with-param name="path"><xsl:value-of select="substring-after($path,'.')"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
    <xsl:if test="contains($path,'_')">
        <xsl:text>../</xsl:text>
        <xsl:call-template name="path">
            <xsl:with-param name="path"><xsl:value-of select="substring-after($path,'_')"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
    <xsl:if test="not(contains($path,'.')) and not(contains($path,'_')) and not(contains($path,'\')) and not($path = '')">
        <xsl:text>../</xsl:text>
    </xsl:if>
</xsl:template>

<!-- create the link to the stylesheet based on the package name -->
<xsl:template name="create.stylesheet.link">
    <xsl:param name="package.name"/>
    <LINK REL ="stylesheet" TYPE="text/css" TITLE="Style"><xsl:attribute name="href"><xsl:if test="not($package.name = 'unnamed package')"><xsl:call-template name="path"><xsl:with-param name="path" select="$package.name"/></xsl:call-template></xsl:if>stylesheet.css</xsl:attribute></LINK>
</xsl:template>

<!-- create the link to the  package summary -->
<xsl:template name="create.package-summary.link">
    <xsl:param name="package.name"/>
    <xsl:param name="fullpackage.name"/>
    <a target="classFrame">
        <xsl:attribute name="href">
            <xsl:if test="not($fullpackage.name = 'unnamed package')">
                <xsl:call-template name="path">
                    <xsl:with-param name="path" select="$fullpackage.name"/>
                </xsl:call-template>
            </xsl:if>
        <xsl:value-of select="translate($package.name,'._\','///')"/>/package-summary.html</xsl:attribute>
        <xsl:value-of select="$package.name"/>
    </a>
</xsl:template>

<!-- alternated row style -->
<xsl:template name="alternate-row">
<xsl:attribute name="class">
  <xsl:if test="position() mod 2 = 1">a</xsl:if>
  <xsl:if test="position() mod 2 = 0">b</xsl:if>
</xsl:attribute>
</xsl:template>

</xsl:stylesheet>
