# 🫧 Peekabot

**Peekabot** is a modern, lightweight, and expressive PHP SDK for Telegram Bots. Built with an elegant, Laravel-inspired architecture, it helps developers build robust, modular, and maintainable Telegram bots with ease.

---

## ✨ Features

- **Advanced Router & Middleware Pipeline**: Intercept and filter incoming updates using middleware (e.g., Rate Limiting / Anti-Spam) before hitting handlers.
- **Stateful Conversation Wizard**: Multi-step state machine with session timeouts and custom validation fallbacks.
- **Flexible Storage Drivers**: Seamless session management backed by **Redis** or **Database (PDO)**.
- **Fluent UI Builders**: Clean, chainable builders for **Inline Keyboards** and **Reply Keyboards**.
- **Robust Update Dispatcher**: Automatically routes various Telegram update types (`message`, `callback_query`, etc.).
- **Laravel-Style Error Logging**: Centralized error tracking (`error.log`) for effortless debugging.

---

## 📦 Installation

Require the package in your PHP project via Composer (requires PHP >= 8.2):

```bash
composer require peekabot/sdk
```

---

## 🛠️ Quick Start (`index.php`)

Here is how to set up Peekabot as a Webhook endpoint:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Peekabot\Bot;
use Peekabot\Context;
use Peekabot\Keyboard\InlineKeyboard;
use Peekabot\Logger;

// 1. Set the centralized error log directory
Logger::setPath(__DIR__);

// 2. Initialize the Bot with your token from @BotFather
$bot = new Bot('YOUR_BOT_TOKEN_HERE');

// 3. Register a /start command with an Inline Keyboard
$bot->router()->command('start', function (Context $ctx) {
    $keyboard = (new InlineKeyboard())
        ->row(['🚀 Click Me' => 'action:test', '❓ Help' => 'action:help']);

    $ctx->reply("Hello! Welcome to Peekabot.", [
        'reply_markup' => $keyboard->toArray()
    ]);
});

// 4. Handle inline button callbacks (Callback Query)
$bot->router()->callback('action:test', function (Context $ctx) {
    $ctx->reply("Awesome! Your callback query was handled successfully.");
});

// 5. Run the bot dispatcher
$bot->run();
```

---

## 📂 Project Structure

```text
peekabot/
├── src/
│   ├── Bot.php                  # Main bot entry point
│   ├── Request.php              # Telegram cURL HTTP wrapper
│   ├── Context.php              # Update wrapper & helper methods ($ctx->reply())
│   ├── Logger.php               # Centralized error logging (error.log)
│   ├── Router/
│   │   ├── Router.php           # Routing engine (commands, text, regex, callbacks)
│   │   └── Route.php            # Individual route & middleware definition
│   ├── Middleware/
│   │   ├── MiddlewareInterface.php
│   │   └── ThrottleMiddleware.php # Rate limiting / anti-spam
│   ├── Conversation/
│   │   ├── ConversationManager.php# Multi-step state machine engine
│   │   └── Step.php             # Individual wizard steps
│   ├── Storage/
│   │   ├── StorageInterface.php # Storage driver contract
│   │   ├── RedisStorage.php     # Redis storage driver
│   │   └── DatabaseStorage.php  # Database (PDO) storage driver
│   ├── Keyboard/
│   │   ├── InlineKeyboard.php   # Inline keyboard builder
│   │   └── ReplyKeyboard.php    # Reply keyboard builder
│   └── Exceptions/
│       ├── TelegramException.php
│       └── ConversationException.php
├── composer.json
└── README.md
```

---

## ⚙️ Local Development & Testing (Laravel Herd / Expose)

1. Start an HTTPS tunnel in your project directory:
   ```bash
   expose
   ```
2. Register your webhook URL to Telegram via browser or cURL:
   ```text
   https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://<your-subdomain>.sharedwithexpose.com/index.php
   ```
3. Open Telegram and send `/start` to your bot!

---

## 📄 License

Peekabot is open-sourced software licensed under the [MIT license](LICENSE).
