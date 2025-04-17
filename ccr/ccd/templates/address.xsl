<?xml version="1.0" encoding="UTF-8"?>
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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:a="urn:astm-org:CCR" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:template name="address">
        <xsl:param name="actorID"/>
        <xsl:variable name="actor" select="/a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$actorID]"/>
        <td>
            <table class="internal" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top">
                        <xsl:for-each select="$actor/a:Address">
                            <xsl:if test="a:Type">
                                <b>
                                    <xsl:value-of select="a:Type/a:Text"/>:
                                </b>
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
                                <xsl:value-of select="a:City"/>,<xsl:text xml:space="preserve"> </xsl:text>
                            </xsl:if>
                            <xsl:value-of select="a:State"/>
                            <xsl:text xml:space="preserve"> </xsl:text>
                            <xsl:value-of select="a:PostalCode"/>
                            <br/>
                        </xsl:for-each>
                    </td>
                    <td valign="top">
                        <xsl:for-each select="a:Telephone">
                            <xsl:if test="a:Type/a:Text">
                                <xsl:value-of select="a:Type/a:Text"/> Phone:<xsl:text xml:space="preserve"> </xsl:text>
                            </xsl:if>
                            <xsl:value-of select="a:Value"/>
                        </xsl:for-each>
                        <xsl:for-each select="a:EMail">
                            Email:<xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Value"/>
                        </xsl:for-each>
                    </td>
                </tr>
            </table>
        </td>
    </xsl:template>
</xsl:stylesheet>
