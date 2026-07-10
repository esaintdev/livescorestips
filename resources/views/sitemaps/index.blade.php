<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{{ asset('sitemap-index.xsl') }}"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach([
        'main' => route('sitemap.main'),
        'blog' => route('sitemap.blog'),
        'seo' => route('sitemap.seo'),
        'predictions' => route('sitemap.predictions'),
    ] as $key => $location)
        <sitemap>
            <loc>{{ $location }}</loc>
            @if(!empty($lastModified[$key]))
                <lastmod>{{ \Carbon\Carbon::parse($lastModified[$key])->toAtomString() }}</lastmod>
            @endif
        </sitemap>
    @endforeach
</sitemapindex>
