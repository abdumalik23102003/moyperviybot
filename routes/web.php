<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/bot/webhook', function (Request $request) {
    // Telegram'dan kelgan ma'lumotni olish
    $update = $request->all();

    // Agar xabar kelgan bo'lsa va unda matn bo'lsa
    if (isset($update['message'])) {
        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'] ?? '';

        // Agar foydalanuvchi /start yozgan bo'lsa
        if ($text === '/start') {
            $botToken = env('TELEGRAM_BOT_TOKEN');

            // Telegram API orqali javob yuborish
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => 'Salom! Botimizga xush kelibsiz! 🚀',
            ]);
        }
    }

    return response()->json(['status' => 'success']);
});
