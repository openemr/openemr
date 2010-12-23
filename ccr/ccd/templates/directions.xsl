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
									<xsl:value-of select="a:Dose/a:Value"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Dose/a:Units/a:Unit"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Route/a:Text"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Frequency/a:Value"/>
									<xsl:if test="a:Duration"><xsl:text xml:space="preserve"> </xsl:text>(for <xsl:value-of select="a:Duration/a:Value"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Duration/a:Units/a:Unit"/>)
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
									<xsl:value-of select="a:Dose/a:Value"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Dose/a:Units/a:Unit"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Route/a:Text"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Frequency/a:Value"/>
									<xsl:if test="a:Duration"><xsl:text xml:space="preserve"> </xsl:text>(for <xsl:value-of select="a:Duration/a:Value"/><xsl:text xml:space="preserve"> </xsl:text><xsl:value-of select="a:Duration/a:Units/a:Unit"/>)
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
</xsl:stylesheet>
