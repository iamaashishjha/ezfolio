{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0">
    <channel>
        <title>{{ $portfolioConfig['seo']['title'] ?: $about->name }} Blog</title>
        <link>{{ url('/blog') }}</link>
        <description>{{ $portfolioConfig['seo']['description'] }}</description>
        <language>en</language>
        @foreach ($posts as $post)
            <item>
                <title><![CDATA[{{ $post->title }}]]></title>
                <link>{{ url('/blog/' . $post->slug) }}</link>
                <guid>{{ url('/blog/' . $post->slug) }}</guid>
                <pubDate>{{ ($post->published_at ?: $post->created_at)->toRssString() }}</pubDate>
                <description><![CDATA[{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 200) }}]]></description>
            </item>
        @endforeach
    </channel>
</rss>
