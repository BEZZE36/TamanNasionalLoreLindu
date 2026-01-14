<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Enable response compression for text-based responses.
     * Note: This is a fallback - Apache/Nginx should handle this in production.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip if already compressed or not compressible
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Check if client accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');

        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Only compress text-based responses
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'image/svg+xml',
        ];

        $shouldCompress = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $shouldCompress = true;
                break;
            }
        }

        if (!$shouldCompress) {
            return $response;
        }

        $content = $response->getContent();

        // Only compress if content is large enough
        if (strlen($content) < 1024) {
            return $response;
        }

        $compressed = gzencode($content, 6);

        if ($compressed !== false && strlen($compressed) < strlen($content)) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->remove('Content-Length');
        }

        return $response;
    }
}
