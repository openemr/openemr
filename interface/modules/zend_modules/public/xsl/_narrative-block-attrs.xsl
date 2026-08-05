<?xml version="1.0" encoding="UTF-8"?>
<!--
  Attribute allowlist for CDA R2 narrative-block elements.

  Sourced from HL7's normative NarrativeBlock.xsd
  (github.com/HL7/CDA-core-2.0/tree/master/schema/normative/processable/coreschemas).
  Union across every StrucDoc.* complexType in that schema.

  Attributes copied from input CDA XML into the rendered HTML preview are
  filtered against this list. Anything not on the list is dropped rather
  than copied, which blocks event-handler injection (onmouseover, onclick,
  onfocus, ...) and other non-narrative HTML attributes.

  XML attribute names are case-sensitive; note `ID` is uppercase per spec.
  A lowercase `id` submitted in input CDA would not match and would be
  dropped, which matches spec behavior (only `ID` is legal on narrative
  elements).

  Imported by cda.xsl, ccd.xsl, and qrda.xsl. Single source of truth: when
  extending the allowlist, edit `$narrative-block-attr-allowlist` here and
  every consumer picks it up automatically.
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

  <!--
    Space-delimited allowlist. Space-padded so `contains()` membership
    checks are whole-token: contains(' A B C ', concat(' ', $name, ' ')).
    That prevents e.g. `nameEqualsSomething` accidentally matching `name`.
  -->
  <xsl:variable name="narrative-block-attr-allowlist"
    select="' ID abbr align axis border cellpadding cellspacing char charoff colspan frame headers href language listType mediaType name referencedObject rel rev revised rowspan rules scope span styleCode summary title valign width '"/>

  <!--
    URL-bearing attributes additionally require a recognized scheme
    prefix. The plain allowlist above accepts `href` on narrative
    elements, but XSL doesn't distinguish `href="https://example.org"`
    from `href="data:text/html,..."` without this extra check —
    narrow to the schemes real CDA narrative content actually uses.

    `#` is included so fragment-only links (in-page anchors) still work.
  -->
  <xsl:variable name="narrative-block-url-attrs"
    select="' href '"/>

  <!--
    Iterate every attribute on the context element and emit a copy of
    each one whose local-name is on the allowlist. Non-allowlisted
    attributes are silently dropped. For URL-bearing attributes, the
    value must additionally start with a safe scheme prefix.

    Use from ccd.xsl / qrda.xsl in place of `<xsl:copy-of select="@*"/>`.
  -->
  <xsl:template name="safe-copy-narrative-attrs">
    <xsl:for-each select="@*">
      <xsl:variable name="attr-name" select="local-name(.)"/>
      <xsl:if test="contains($narrative-block-attr-allowlist,
                             concat(' ', $attr-name, ' '))">
        <xsl:choose>
          <xsl:when test="contains($narrative-block-url-attrs,
                                   concat(' ', $attr-name, ' '))">
            <xsl:call-template name="copy-if-safe-url"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:copy-of select="."/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <!--
    Emit the current attribute only if its value starts with one of the
    safe URL prefixes. Called with an attribute node as context.
  -->
  <xsl:template name="copy-if-safe-url">
    <xsl:variable name="value" select="."/>
    <xsl:if test="starts-with($value, 'http://')
               or starts-with($value, 'https://')
               or starts-with($value, 'mailto:')
               or starts-with($value, 'tel:')
               or starts-with($value, '#')">
      <xsl:copy-of select="."/>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>
