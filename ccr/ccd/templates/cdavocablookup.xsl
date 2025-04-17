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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template name="CDAVocabularyLookup">
        <xsl:param name="domain"/>
        <xsl:param name="ccrtext"/>

        <xsl:variable name="map" select="document('cdavocabmap.xml')"/>
        <xsl:variable name="ccrtext_uc" select="translate($ccrtext, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
        <xsl:variable name="cdaCodeMatch" select="$map/domains/domain[@name=$domain]/item[translate(cdacode,'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')=$ccrtext_uc]/cdacode"/>
        <xsl:choose>
            <xsl:when test="$cdaCodeMatch">
                <xsl:value-of select="$cdaCodeMatch"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$map/domains/domain[@name=$domain]/item[translate(ccrtext,'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')=$ccrtext_uc]/cdacode"/>    
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="CDADisplayNameLookup">
        <xsl:param name="domain"/>
        <xsl:param name="cdacode"/>
        <xsl:variable name="map" select="document('cdavocabmap.xml')"/>
        <xsl:choose>
            <xsl:when test="$map/domains/domain[@name=$domain]/item[cdacode=$cdacode]/cdadisplayname">
                <xsl:value-of select="$map/domains/domain[@name=$domain]/item[cdacode=$cdacode]/cdadisplayname"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$map/domains/domain[@name=$domain]/item[cdacode=$cdacode]/ccrtext"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="CDAVocabularyCodeSystemNameLookup">
        <xsl:param name="domain"/>
        
        <xsl:value-of select="document('cdavocabmap.xml')/domains/domain[@name=$domain]/@codeSystemName"/>
    </xsl:template>

    <xsl:template name="CDAVocabularyCodeSystemLookup">
        <xsl:param name="domain"/>

        <xsl:value-of select="document('cdavocabmap.xml')/domains/domain[@name=$domain]/@codeSystem"/>
    </xsl:template>
    
</xsl:stylesheet>
