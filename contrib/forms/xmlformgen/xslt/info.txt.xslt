<?xml version="1.0"?>
<!--
Copyright (C) 2009 Julia Longtin <julia.longtin@gmail.com>

This program is free software; you can redistribute it and/or
Modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 -->
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes"/>
<xsl:template match="/">
    <xsl:value-of select="form/RealName" />
<xsl:text disable-output-escaping="yes"><![CDATA[
]]></xsl:text>
</xsl:template>
</xsl:stylesheet>
