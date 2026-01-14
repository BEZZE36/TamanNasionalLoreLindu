export default {
    site: 'http://127.0.0.1:8000',
    scanner: {
        // Maximum number of pages to scan
        maxRoutes: 100,
        // Ignore patterns
        ignoreRoutes: [
            '/admin/*',
            '/login',
            '/register',
            '/api/*',
            '/storage/*',
        ],
        // Crawl the site for routes
        samples: 1,
    },
    // Lighthouse options
    lighthouse: {
        // Categories to audit
        onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
        // Desktop settings
        formFactor: 'desktop',
        screenEmulation: {
            mobile: false,
            width: 1350,
            height: 940,
            deviceScaleFactor: 1,
            disabled: false,
        },
        throttling: {
            rttMs: 40,
            throughputKbps: 10240,
            cpuSlowdownMultiplier: 1,
        },
    },
    // Output settings
    routerPrefix: '/_unlighthouse',
    outputPath: '.unlighthouse',
    // Development settings
    debug: false,
    // Chrome options
    puppeteerOptions: {
        headless: 'new',
    },
    // Hooks to run before lighthouse
    hooks: {},
}
