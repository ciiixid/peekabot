<?php

namespace TeleCore;

class Context
{
    protected array $update;
    protected Bot $bot;
    protected ?array $message = null;
    protected ?array $callbackQuery = null;

    public function __construct(array $update, Bot $bot)
    {
        $this->update = $update;
        $this->bot = $bot;

        if (isset($update['message'])) {
            $this->message = $update['message'];
        } elseif (isset($update['edited_message'])) {
            $this->message = $update['edited_message'];
        } elseif (isset($update['callback_query'])) {
            $this->callbackQuery = $update['callback_query'];
            $this->message = $this->callbackQuery['message'] ?? null;
        }
    }

    public function getUpdate(): array
    {
        return $this->update;
    }

    public function getChatId(): ?int
    {
        if ($this->message && isset($this->message['chat']['id'])) {
            return $this->message['chat']['id'];
        }
        return null;
    }

    public function getUserId(): ?int
    {
        if ($this->callbackQuery && isset($this->callbackQuery['from']['id'])) {
            return $this->callbackQuery['from']['id'];
        }
        if ($this->message && isset($this->message['from']['id'])) {
            return $this->message['from']['id'];
        }
        return null;
    }

    public function getText(): ?string
    {
        return $this->message['text'] ?? $this->callbackQuery['data'] ?? null;
    }

    public function reply(string $text, array $extra = []): mixed
    {
        $chatId = $this->getChatId();
        if (!$chatId) return null;

        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $extra);

        return $this->bot->getRequest()->call('sendMessage', $params);
    }
}