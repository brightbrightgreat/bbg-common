<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:html="http://www.w3.org/TR/REC-html40"
	xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />

	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<meta name="robots" content="noindex,follow" />
				<style type="text/css">
					<![CDATA[
					* {
						box-sizing: border-box;
						margin: 0;
						padding: 0;
					}

					body {
						font-family: 'Fira Mono', Consolas, Monaco, monospace;
						font-size: 1rem;
						line-height: 1.5;
						letter-spacing: .1em;
						color: #555;
					}

					header,
					footer {
						background-color: #555;
						color: #fff;
						padding: 20px;
						margin-bottom: 100px;
						line-height: 1;
					}

					h1 {
						font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
						font-size: 1.25rem;
						font-weight: 300;
					}

					footer {
						margin-bottom: 0;
						margin-top: 100px;

						font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
						font-size: 1rem;
						font-weight: 400;
					}

					table {
						border-collapse: collapse;
						margin: 0;
						padding: 0;
					}

					th,
					td {
						font-size: .75rem;
						padding: 20px;
						text-align: left;
						vertical-align: top;
					}

					th {
						font-weight: bold;
						text-transform: uppercase;
					}

					td {
						border-top: 1px dashed #ccc;
					}

					a {
						color: #3498db;
						text-decoration: none;
						transition: color .3s ease;
					}

					a:hover {
						color: #000;
					}

					.image-link {
						display: block;
					}
					]]>
				</style>
			</head>
			<body>
				<xsl:apply-templates></xsl:apply-templates>
			</body>
		</html>
	</xsl:template>


	<xsl:template match="sitemap:urlset">
		<header>
        	<h1>XML Sitemap</h1>
        </header>
		<main>
			<table cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th>URL</th>
						<th>Priority</th>
						<th>Changes</th>
						<th>Modified (GMT)</th>
						<th>Image(s)</th>
					</tr>
				</thead>
				<tbody>
					<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
					<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
					<xsl:for-each select="./sitemap:url">
						<tr>
							<td>
								<xsl:variable name="itemURL">
									<xsl:value-of select="sitemap:loc"/>
								</xsl:variable>
								<a href="{$itemURL}">
									<xsl:value-of select="sitemap:loc"/>
								</a>
							</td>
							<td>
								<xsl:value-of select="concat(sitemap:priority*100,'%')"/>
							</td>
							<td>
								<xsl:value-of select="concat(translate(substring(sitemap:changefreq, 1, 1),concat($lower, $upper),concat($upper, $lower)),substring(sitemap:changefreq, 2))"/>
							</td>
							<td>
								<xsl:variable name="dateUpdated">
									<xsl:value-of select="substring(sitemap:lastmod,0,11)"/>
								</xsl:variable>
								<xsl:variable name="datetimeUpdated">
									<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,8)))"/>
								</xsl:variable>
								<abbr title="{$datetimeUpdated}">
									<xsl:value-of select="$dateUpdated"/>
								</abbr>
							</td>
							<td>
								<xsl:for-each select="./image:image">
									<xsl:variable name="imageURL">
										<xsl:value-of select="image:loc"/>
									</xsl:variable>
									<a href="{$imageURL}" class="image-link">
										<xsl:value-of select="image:title"/>
									</a>
								</xsl:for-each>
							</td>
						</tr>
					</xsl:for-each>
				</tbody>
			</table>
		</main>
		<footer>
			The end.
		</footer>
	</xsl:template>
</xsl:stylesheet>
