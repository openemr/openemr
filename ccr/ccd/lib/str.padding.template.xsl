<?xml version="1.0"?>

<!-- This is a public domain script released from http://exslt.org/ -->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="str">

<xsl:template name="str:padding">
	<xsl:param name="length" select="0" />
   <xsl:param name="chars" select="' '" />
   <xsl:choose>
      <xsl:when test="not($length) or not($chars)" />
      <xsl:otherwise>
         <xsl:variable name="string" 
                       select="concat($chars, $chars, $chars, $chars, $chars, 
                                      $chars, $chars, $chars, $chars, $chars)" />
         <xsl:choose>
            <xsl:when test="string-length($string) >= $length">
               <xsl:value-of select="substring($string, 1, $length)" />
            </xsl:when>
            <xsl:otherwise>
               <xsl:call-template name="str:padding">
                  <xsl:with-param name="length" select="$length" />
                  <xsl:with-param name="chars" select="$string" />
               </xsl:call-template>
            </xsl:otherwise>
         </xsl:choose>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

</xsl:stylesheet>
