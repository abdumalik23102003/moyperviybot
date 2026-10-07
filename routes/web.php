<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
 * Telegram webhook.
 *
 * Telegram oddiy POST so'rov yuboradi: unda session, cookie va CSRF token yo'q.
 * Shuning uchun "web" guruhidagi shu middleware'lar olib tashlangan. Aks holda
 * SESSION_DRIVER=database bo'lganda (sukut bo'yicha) har bir so'rov
 * "sessions" jadvaliga / SQLite fayliga murojaat qilib, 500 xato bilan tushadi.
 */
Route::post('/bot/webhook', function (Request $request) {
    try {
        Log::info('Telegram webhook received');

        $update = $request->all();

        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'] ?? '';

            // "/start" va "/start@BotNomi" yoki "/start parametr" ni ham qabul qiladi
            if (str_starts_with($text, '/start')) {
                $botToken = config('services.telegram.bot_token');

                if (empty($botToken)) {
                    Log::error('TELEGRAM_BOT_TOKEN is not set');

                    return response()->json(['status' => 'error', 'message' => 'token not set'], 500);
                }

                $response = Http::timeout(10)->post(
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
})->withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
]);
