<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO: Primary Meta Tags --}}
    <meta name="title" content="{{ config('app.name', 'TNLL Explore') }} - Taman Nasional Lore Lindu">
    <meta name="description"
        content="Jelajahi keindahan Taman Nasional Lore Lindu, rumah bagi keanekaragaman hayati yang luar biasa di Sulawesi Tengah. Temukan flora, fauna endemik, dan budaya megalitik.">
    <meta name="keywords"
        content="TNLL, Taman Nasional Lore Lindu, Sulawesi Tengah, ekowisata, flora, fauna, anoa, maleo, megalitik">
    <meta name="author" content="TNLL Explore">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">

    {{-- SEO: Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('app.name', 'TNLL Explore') }} - Taman Nasional Lore Lindu">
    <meta property="og:description"
        content="Jelajahi keindahan Taman Nasional Lore Lindu, rumah bagi keanekaragaman hayati yang luar biasa di Sulawesi Tengah.">
    <meta property="og:image" content="{{ asset('assets/og-image.jpg') }}">
    <meta property="og:locale" content="id_ID">

    {{-- SEO: Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ config('app.name', 'TNLL Explore') }}">
    <meta property="twitter:description" content="Jelajahi keindahan Taman Nasional Lore Lindu di Sulawesi Tengah.">
    <meta property="twitter:image" content="{{ asset('assets/og-image.jpg') }}">

    {{-- Security Headers --}}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    {{-- Theme Color for PWA --}}
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    {{-- Performance: DNS Prefetch for external resources --}}
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//ui-avatars.com">
    <link rel="dns-prefetch" href="//www.youtube.com">

    <title inertia>{{ config('app.name', 'TNLL Explore') }}</title>

    {{-- Favicon with preload for faster initial display --}}
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    {{-- Font preconnect and preload for faster font loading --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload"
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
    </noscript>

    {{-- Critical CSS inline for above-the-fold content --}}
    <style>
        /* Prevent FOUC (Flash of Unstyled Content) */
        html {
            visibility: visible;
            opacity: 1;
        }

        /* Base font stack fallback */
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Smooth loading transition */
        #app {
            opacity: 1;
            transition: opacity 0.2s ease-in-out;
        }

        /* Reduce CLS for images */
        img,
        video {
            max-width: 100%;
            height: auto;
        }
    </style>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "TNLL Explore",
        "alternateName": "Taman Nasional Lore Lindu",
        "url": "{{ config('app.url') }}",
        "logo": "{{ asset('assets/logo.png') }}",
        "description": "Portal resmi eksplorasi Taman Nasional Lore Lindu, Sulawesi Tengah",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "Sulawesi Tengah",
            "addressCountry": "ID"
        },
        "sameAs": []
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "TNLL Explore",
        "url": "{{ config('app.url') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ config('app.url') }}/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    {{-- Skip to main content for accessibility --}}
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-emerald-600 focus:text-white focus:rounded-lg focus:outline-none">
        Langsung ke konten utama
    </a>

    <div id="main-content">
        @inertia
    </div>
</body>

</html>