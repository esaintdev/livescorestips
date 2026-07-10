<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{{ asset('sitemap-index.xsl') }}"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ route('sitemap.main') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('sitemap.blog') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('sitemap.seo') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('sitemap.predictions') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
</sitemapindex>
