{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page['loc'] }}</loc>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($categories as $category)
    <url>
        <loc>{{ $category->url() }}</loc>
        <lastmod>{{ $category->updated_at?->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>{{ $category->parent_id ? '0.7' : '0.8' }}</priority>
    </url>
@endforeach
@foreach ($listings as $listing)
    <url>
        <loc>{{ route('listings.show', $listing) }}</loc>
        <lastmod>{{ $listing->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        @if ($listing->imageUrl())
            <image:image><image:loc>{{ url($listing->imageUrl()) }}</image:loc></image:image>
        @endif
    </url>
@endforeach
@foreach ($businesses as $business)
    <url>
        <loc>{{ route('business.show', $business) }}</loc>
        <lastmod>{{ $business->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
@endforeach
</urlset>
