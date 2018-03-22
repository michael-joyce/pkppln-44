<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:html="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="xs html"
    version="2.0">
    
    <xsl:template match="phpcs">
        <html>
            <head>
                <title>PHPCS Report</title>
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" />
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
                <style>
                    tr.fixable {
                        color: grey;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <xsl:apply-templates />
                </div>
            </body>
        </html>        
    </xsl:template>
    
    <xsl:template match="file">
        <p><xsl:value-of select="@name"/></p>
        <table class="table table-stripped table-compressed">
            <thead>
                <tr>
                    <th width="100px">Line</th>
                    <th>Message</th>
                </tr>
            </thead>
            <xsl:apply-templates select="error"/>
        </table>
    </xsl:template>
    
    <xsl:template match="error">
        <xsl:variable name="class">
            <xsl:choose>
                <xsl:when test="@fixable=1">fixable</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <tr class="{$class}">
            <td>
                <xsl:value-of select="@line"/>:<xsl:value-of select="@column"/>
            </td>
            <td>
                <xsl:apply-templates/> <br/>
                <xsl:value-of select="@source"/>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    
</xsl:stylesheet>