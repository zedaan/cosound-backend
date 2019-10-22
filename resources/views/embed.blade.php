<!DOCTYPE html>
<html xmlns:og="http://ogp.me/ns#">

<head>
    <meta property="og:site_name" content="Cosound" >
    <meta property="og:title" content="CoSound">
    <meta property="og:url" content="{{ $url }}" >

    @if ($media)
        @if ($media->file_type === 'audio') 
            <meta property="og:type" content="music.song" >
            <meta property="og:audio" content="{{ $media->path }}" >
            <meta property="og:audio:type" content="audio/vnd.facebook.bridge" >
        @elseif ($media->file_type === 'video')
            <meta property="og:type" content="video.other" >
            <meta property="og:video" content="{{ $media->path }}" >
            <meta property="og:video:height" content="620" >
            <meta property="og:video:width" content="385" >
            <meta property="og:video:type" content="application/x-shockwave-flash" >
        @else
            <meta >
        @endif
    @endif

    <meta property="og:image" content="{{ $imageUrl }}" >
    <meta property="og:description" content="{{ $description }}" >
 
    <title>CoSound</title>

    <!-- Twitter -->
    <meta name="twitter:title" content="CoSound" />
    <meta name="twitter:description" content="{{ $description }}" />
    <meta name="twitter:image" content="{{ $imageUrl }}" />

    @if (! $media)
        <meta name="twitter:card" content="summary" />
    @elseif ($media->file_type !== 'image')
        <meta name="twitter:card" content="player" />
        <meta name="twitter:player:width" content="512" />
        <meta name="twitter:player:height" content="512" />
        <meta name="twitter:player" content="{{ $media->path }}" />
    @endif

    
    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "name": "CoSound",
        "description": "{{ $description or "" }}",
        "thumbnailUrl": "{{ $imageUrl or "" }}",
        "url": "{{ $url }}",
        "uploadDate": "{{ $post->created_at }}",

        @if ($media)
            @if ($media->file_type === 'audio')
                "@type": "AudioObject",
            @elseif ($media->file_type === 'video')
                "@type": "VideoObject",
            @else
                "@type": "ImageObject",
            @endif
            "contentUrl": "{{ $media->path or '' }}"
        @endif
    }
    </script>

</head>

<body></body>

</html>