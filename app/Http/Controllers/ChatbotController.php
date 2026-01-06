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
            $apiKey = env('GEMINI_API_KEY');
            
            if (empty($apiKey)) {
                Log::error('Gemini API Key is missing');
                return response()->json([
                    'reply' => 'API Key tidak dikonfigurasi. Hubungi administrator.'
                ], 500);
            }
            
            Log::info('Calling Gemini API with key: ' . substr($apiKey, 0, 10) . '...');
            
            // Direct HTTP call to Gemini API with timeout
            // Note: withoutVerifying() is used for local development due to Laragon SSL cert issues
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $systemContext . "\n\nUser: " . $userMessage . "\nAI:"
                                ]
                            ]
                        ]
                    ]
                ]);

            Log::info('Gemini API Response Status: ' . $response->status());
            Log::info('Gemini API Response Body: ' . substr($response->body(), 0, 500));

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses permintaan Anda.';
                
                Log::info('Chatbot reply: ' . substr($reply, 0, 100));
                
                return response()->json([
                    'reply' => $reply
                ]);
            } else {
                Log::error('Gemini API Error Status: ' . $response->status());
                Log::error('Gemini API Error Body: ' . $response->body());
                return response()->json([
                    'reply' => 'API Error: ' . $response->status() . ' - Silakan coba lagi.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Gemini Chatbot Exception: ' . $e->getMessage());
            Log::error('Gemini Chatbot Trace: ' . $e->getTraceAsString());
            return response()->json([
                'reply' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

