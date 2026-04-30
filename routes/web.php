<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});



Route::post('/chat', function (Request $request) {

    $apiKey = env('GROQ_API_KEY');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type'  => 'application/json',
    ])->post('https://api.groq.com/openai/v1/chat/completions', [
        'model'    => 'llama-3.3-70b-versatile', // free & fast
        'messages' => [
            ['role' => 'user', 'content' => $request->message]
        ],
        'max_tokens' => 1024,
    ]);

    $data = $response->json();

    $reply = $data['choices'][0]['message']['content'] ?? null;

    if ($reply) {
        return response()->json(['reply' => $reply]);
    }

    return response()->json([
        'error' => '❌ ' . ($data['error']['message'] ?? 'Unknown error')
    ]);
});
