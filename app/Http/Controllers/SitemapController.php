<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $destinations = Destination::active()->get();
        $articles = \App\Models\Article::published()->get();
        $flora = \App\Models\Flora::active()->get();
        $fauna = \App\Models\Fauna::active()->get();

        $content = view('sitemap', compact('destinations', 'articles', 'flora', 'fauna'));
        
        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
