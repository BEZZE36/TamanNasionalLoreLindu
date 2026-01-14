<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');

        Log::info('Chatbot request received: ' . $userMessage);

        // System Instruction / Context
        $systemContext = "Anda adalah asisten virtual cerdas untuk website Taman Nasional Lore Lindu (TNLL). 
        Tujuan anda adalah membantu pengunjung dengan informasi akurat tentang TNLL.
        
        Informasi Kunci TNLL:
        - Nama: Taman Nasional Lore Lindu.
        - Lokasi: Sulawesi Tengah, Indonesia (Jantung Wallacea).
        - Luas: ± 217.991 hektar.
        - Status: Cagar Biosfer UNESCO (sejak 1977) dan ASEAN Heritage Park.
        - Fauna: 267+ spesies burung, 80% mamalia endemik Sulawesi (Anoa, Babirusa, Tarsius, Kuskus Beruang).
        - Flora: Wanga, Leda, Anggrek Hitam, Kantong Semar.
        - Wisata Megalitikum: Lembah Bada, Besoa, Napu (Patung megalit usia ribuan tahun).
        - Wisata Alam: Danau Lindu, Danau Tambing (Surga Birdwatching), Gunung Nokilalaki.
        
        Gaya Bicara: Ramah, membantu, informatif, dan profesional.
        Bahasa: Jawablah dalam bahasa yang sama dengan pertanyaan pengguna (Indonesia atau Inggris).
        
        Jika ditanya tentang pembelian tiket, arahkan ke menu 'Destinasi' atau tombol 'Beli Tiket'.
        Jika pertanyaan diluar konteks TNLL, jawab dengan sopan bahwa anda hanya bisa menjawab seputar TNLL.";

        try {
            $apiKey = config('openrouter.api_key');
            $model = config('openrouter.model');
            $endpoint = config('openrouter.endpoint');

            if (empty($apiKey)) {
                Log::error('OpenRouter API Key is missing');
                return response()->json([
                    'reply' => 'API Key tidak dikonfigurasi. Hubungi administrator.'
                ], 500);
            }

            Log::info('Calling OpenRouter API', [
                'model' => $model
            ]);

            $response = Http::timeout(60)
                ->withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => config('openrouter.site_url', config('app.url')),
                    'X-Title' => config('openrouter.site_name', config('app.name')),
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemContext
                        ],
                        [
                            'role' => 'user',
                            'content' => $userMessage
                        ]
                    ]
                ]);

            Log::info('OpenRouter API Response Status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'Maaf, saya tidak dapat memproses permintaan Anda.';

                Log::info('Chatbot reply: ' . substr($reply, 0, 100));

                return response()->json([
                    'reply' => $reply
                ]);
            } else {
                Log::error('OpenRouter API Error', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500)
                ]);

                if ($response->status() === 429) {
                    return response()->json([
                        'reply' => 'Layanan AI sedang sibuk. Silakan coba lagi dalam beberapa saat.'
                    ], 503);
                }

                return response()->json([
                    'reply' => 'Terjadi kesalahan pada layanan AI. Silakan coba lagi.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('OpenRouter Chatbot Exception: ' . $e->getMessage());
            return response()->json([
                'reply' => 'Terjadi kesalahan. Silakan coba lagi nanti.'
            ], 500);
        }
    }
}
