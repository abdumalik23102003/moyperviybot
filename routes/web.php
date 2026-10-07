<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/bot/webhook', function (Request $request) {
    try {
        Log::info('Telegram webhook received');

        $update = $request->all();

        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'] ?? '';

            if ($text === '/start') {
                $botToken = env('TELEGRAM_BOT_TOKEN');

                Log::info('Telegram token exists', [
                    'exists' => !empty($botToken),
                ]);

                $response = Http::post(
                    "https://api.telegram.org/bot{$botToken}/sendMessage",
                    [
                        'chat_id' => $chatId,
                        'text' => 'Salom! Botimizga xush kelibsiz! 🚀',
                    ]
                );

                Log::info('Telegram API response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
        ]);

    } catch (\Throwable $e) {
        Log::error('Telegram webhook error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
