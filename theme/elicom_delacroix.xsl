<?xml version='1.0' encoding='UTF-8'?>
<xsl:transform version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns="http://www.w3.org/1999/xhtml" 
  xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="elicom_html.xsl"/>
  <xsl:output encoding="UTF-8" indent="yes" media-type="text/html" method="xml" omit-xml-declaration="yes"/>
  
  
  <xsl:template name="href">
    <xsl:param name="url" select="@target"/>
    <xsl:choose>
      <xsl:when test="starts-with($url, 'http')">
        <xsl:value-of select="$url"/>
      </xsl:when>
      <xsl:when test="contains($url, '.xml')">
        <xsl:value-of select="substring-before($url, '.xml')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$url"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  

</xsl:transform>
