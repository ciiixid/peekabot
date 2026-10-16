<?php
Logger::setPath(__DIR__);
use TeleCore\Bot;
use TeleCore\Context;
use TeleCore\Keyboard\InlineKeyboard;
use TeleCore\Keyboard\ReplyKeyboard;

$bot = new Bot('YOUR_TELEGRAM_BOT_TOKEN');

$bot->router()->command('start', function (Context $ctx) {
    $keyboard = (new InlineKeyboard())
        ->row(['🚀 Tes Tombol' => 'action:test', '✨ Bantuan' => 'action:help']);

    $ctx->reply("Halo! TeleCore berhasil berjalan di Laravel Herd 🐘🔥", [
        'reply_markup' => $keyboard->toArray()
    ]);
});

$bot->router()->callback('action:test', function (Context $ctx) {
    $ctx->reply("Mantap! Tombol inline berhasil di-klik dan di-handle oleh Router TeleCore.");
});

$bot->run();