<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <meta name="title" content="<?php echo e(config('app.name', 'TNLL Explore')); ?> - Taman Nasional Lore Lindu">
    <meta name="description"
        content="Jelajahi keindahan Taman Nasional Lore Lindu, rumah bagi keanekaragaman hayati yang luar biasa di Sulawesi Tengah. Temukan flora, fauna endemik, dan budaya megalitik.">
    <meta name="keywords"
        content="TNLL, Taman Nasional Lore Lindu, Sulawesi Tengah, ekowisata, flora, fauna, anoa, maleo, megalitik">
    <meta name="author" content="TNLL Explore">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">

    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:title" content="<?php echo e(config('app.name', 'TNLL Explore')); ?> - Taman Nasional Lore Lindu">
    <meta property="og:description"
        content="Jelajahi keindahan Taman Nasional Lore Lindu, rumah bagi keanekaragaman hayati yang luar biasa di Sulawesi Tengah.">
    <meta property="og:image" content="<?php echo e(asset('assets/og-image.jpg')); ?>">
    <meta property="og:locale" content="id_ID">

    
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(url()->current()); ?>">
    <meta property="twitter:title" content="<?php echo e(config('app.name', 'TNLL Explore')); ?>">
    <meta property="twitter:description" content="Jelajahi keindahan Taman Nasional Lore Lindu di Sulawesi Tengah.">
    <meta property="twitter:image" content="<?php echo e(asset('assets/og-image.jpg')); ?>">

    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//ui-avatars.com">
    <link rel="dns-prefetch" href="//www.youtube.com">

    <title inertia><?php echo e(config('app.name', 'TNLL Explore')); ?></title>

    
    <link rel="icon" href="<?php echo e(asset('assets/logo.png')); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/logo.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">

    
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
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "TNLL Explore",
        "alternateName": "Taman Nasional Lore Lindu",
        "url": "<?php echo e(config('app.url')); ?>",
        "logo": "<?php echo e(asset('assets/logo.png')); ?>",
        "description": "Portal resmi eksplorasi Taman Nasional Lore Lindu, Sulawesi Tengah",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Sulawesi Tengah",
            "addressCountry": "ID"
        },
        "sameAs": []
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TNLL Explore",
        "url": "<?php echo e(config('app.url')); ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo e(config('app.url')); ?>/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
</head>

<body class="font-sans antialiased">
    
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-emerald-600 focus:text-white focus:rounded-lg focus:outline-none">
        Langsung ke konten utama
    </a>

    <div id="main-content">
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } elseif (config('inertia.use_script_element_for_initial_page')) { ?><script data-page="app" type="application/json"><?php echo json_encode($page); ?></script><div id="app"></div><?php } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
    </div>
</body>

</html><?php /**PATH /media/bezze_01/33B420DF64638EF2/tnll/resources/views/app.blade.php ENDPATH**/ ?>