<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
    exclude-result-prefixes="s">
    <xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="no"/>
    <xsl:template match="/">
        <html lang="en">
            <head>
                <meta charset="utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <meta name="robots" content="noindex,follow"/>
                <link rel="icon" type="image/png" href="/images/icon.png"/>
                <title>XML Sitemap — LiveScores Tips</title>
                <link rel="preconnect" href="https://fonts.googleapis.com"/>
                <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        margin: 0;
                        font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
                        background: #E8E8E8;
                        color: #171717;
                        line-height: 1.5;
                        min-height: 100vh;
                    }
                    .wrap { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
                    header {
                        background: #171717;
                        color: #fff;
                        padding: 1.25rem 1.5rem;
                        margin: -1.5rem -1.25rem 1.75rem;
                        border-radius: 0 0 1rem 1rem;
                        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
                    }
                    header h1 {
                        margin: 0 0 0.35rem;
                        font-size: 1.35rem;
                        font-weight: 700;
                        letter-spacing: -0.02em;
                    }
                    header p {
                        margin: 0;
                        font-size: 0.875rem;
                        color: #a3a3a3;
                    }
                    header .accent {
                        display: inline-block;
                        width: 2.5rem;
                        height: 3px;
                        background: #ff6b00;
                        border-radius: 2px;
                        margin-bottom: 0.75rem;
                    }
                    header .back-link {
                        display: inline-block;
                        margin-top: 0.5rem;
                        font-size: 0.8rem;
                    }
                    header .back-link a {
                        color: #ff6b00;
                        text-decoration: none;
                    }
                    header .back-link a:hover { text-decoration: underline; }
                    .card {
                        background: #fff;
                        border-radius: 1rem;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                        border: 1px solid rgba(0,0,0,0.06);
                        overflow: hidden;
                    }
                    .meta {
                        padding: 1rem 1.25rem;
                        background: #fafafa;
                        border-bottom: 1px solid #eee;
                        font-size: 0.8125rem;
                        color: #5a5a5a;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 0.8125rem;
                    }
                    th {
                        text-align: left;
                        padding: 0.75rem 1rem;
                        background: #171717;
                        color: #fff;
                        font-weight: 600;
                        font-size: 0.7rem;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                    }
                    td {
                        padding: 0.65rem 1rem;
                        border-bottom: 1px solid #eee;
                        vertical-align: top;
                        word-break: break-all;
                    }
                    tr:nth-child(even) td { background: #fafafa; }
                    tr:hover td { background: #fff3e6; }
                    a.loc {
                        color: #ff6b00;
                        text-decoration: none;
                        font-weight: 500;
                    }
                    a.loc:hover { text-decoration: underline; }
                    .muted { color: #737373; font-size: 0.8rem; }
                    .badge {
                        display: inline-block;
                        padding: 0.15rem 0.5rem;
                        border-radius: 3px;
                        font-size: 0.7rem;
                        font-weight: 600;
                        text-transform: uppercase;
                    }
                    .badge-daily { background: #e8f5e9; color: #2e7d32; }
                    .badge-weekly { background: #e3f2fd; color: #1565c0; }
                    .badge-monthly { background: #fce4ec; color: #c62828; }
                    .badge-never { background: #f3f3f3; color: #616161; }
                    footer {
                        margin-top: 2rem;
                        text-align: center;
                        font-size: 0.75rem;
                        color: #737373;
                    }
                    footer a { color: #ff6b00; text-decoration: none; }
                    footer a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <div class="wrap">
                    <header>
                        <div class="accent"/>
                        <h1>XML Sitemap</h1>
                        <p><xsl:value-of select="/s:urlset/s:url[1]/s:loc"/> and <xsl:value-of select="count(/s:urlset/s:url) - 1"/> more URLs</p>
                        <div class="back-link"><a href="/sitemap.xml">← Back to sitemap index</a></div>
                    </header>
                    <div class="card">
                        <div class="meta">
                            <strong><xsl:value-of select="count(s:urlset/s:url)"/></strong> URLs
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Last modified</th>
                                    <th>Change freq</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="s:urlset/s:url">
                                    <xsl:sort select="s:loc" order="ascending"/>
                                    <tr>
                                        <td><a class="loc" href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                                        <td class="muted">
                                            <xsl:choose>
                                                <xsl:when test="s:lastmod"><xsl:value-of select="s:lastmod"/></xsl:when>
                                                <xsl:otherwise>—</xsl:otherwise>
                                            </xsl:choose>
                                        </td>
                                        <td>
                                            <xsl:choose>
                                                <xsl:when test="s:changefreq='daily'"><span class="badge badge-daily">daily</span></xsl:when>
                                                <xsl:when test="s:changefreq='weekly'"><span class="badge badge-weekly">weekly</span></xsl:when>
                                                <xsl:when test="s:changefreq='monthly'"><span class="badge badge-monthly">monthly</span></xsl:when>
                                                <xsl:otherwise><span class="badge badge-never"><xsl:value-of select="s:changefreq"/></span></xsl:otherwise>
                                            </xsl:choose>
                                        </td>
                                        <td class="muted"><xsl:value-of select="s:priority"/></td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </div>
                    <footer>
                        <a href="/">Home</a>
                        ·
                        <a href="/sitemap.xml">Sitemap index</a>
                        ·
                        <a href="/robots.txt">robots.txt</a>
                    </footer>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
