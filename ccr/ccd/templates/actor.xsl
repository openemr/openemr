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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:a="urn:astm-org:CCR"  xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <!-- Returns the name of the actor, if there is no name it returns the ActorObjectID that was passed in -->
    <xsl:template name="actorName">
        <xsl:param name="objID"/>
        <xsl:variable name="actor" select="//a:ContinuityOfCareRecord/a:Actors/a:Actor[a:ActorObjectID=$objID]"/>
        <xsl:choose>
            <xsl:when test="$actor/a:Person">
                <xsl:choose>
                    <xsl:when test="$actor/a:Person/a:Name/a:DisplayName">
                        <xsl:value-of select="$actor/a:Person/a:Name/a:DisplayName"/>
                    </xsl:when>
                    <xsl:when test="$actor/a:Person/a:Name/a:CurrentName">
                        <xsl:value-of select="$actor/a:Person/a:Name/a:CurrentName/a:Given"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:CurrentName/a:Middle"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:CurrentName/a:Family"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:CurrentName/a:Suffix"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:CurrentName/a:Title"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                    </xsl:when>
                    <xsl:when test="$actor/a:Person/a:Name/a:BirthName">
                        <xsl:value-of select="$actor/a:Person/a:Name/a:BirthName/a:Given"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:BirthName/a:Middle"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:BirthName/a:Family"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:BirthName/a:Suffix"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                        <xsl:value-of select="$actor/a:Person/a:Name/a:BirthName/a:Title"/>
                        <xsl:text xml:space="preserve"> </xsl:text>
                    </xsl:when>
                    <xsl:when test="$actor/a:Person/a:Name/a:AdditionalName">
                        <xsl:for-each select="$actor/a:Person/a:Name/a:AdditionalName">
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
            <xsl:when test="$actor/a:Organization">
                <xsl:value-of select="$actor/a:Organization/a:Name"/>
            </xsl:when>
            <xsl:when test="$actor/a:InformationSystem">
                <xsl:value-of select="$actor/a:InformationSystem/a:Name"/>
                <xsl:text xml:space="preserve"> </xsl:text>
                <xsl:if test="$actor/a:InformationSystem/a:Version">
                    <xsl:value-of select="$actor/a:InformationSystem/a:Version"/>
                    <xsl:text xml:space="preserve"> </xsl:text>
                </xsl:if>
                <xsl:if test="$actor/a:InformationSystem/a:Type">
                    (<xsl:value-of select="$actor/a:InformationSystem/a:Type"/>)
                </xsl:if>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$objID"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <!-- End actorname template -->
</xsl:stylesheet>
