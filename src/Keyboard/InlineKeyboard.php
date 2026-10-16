<?php

namespace TeleCore\Keyboard;

class InlineKeyboard
{
    protected array $inlineKeyboard = [];

    public function row(array $buttons): self
    {
        $formattedRow = [];
        foreach ($buttons as $text => $callbackData) {
            $formattedRow[] = [
                'text' => $text,
                'callback_data' => is_string($callbackData) ? $callbackData : json_encode($callbackData)
            ];
        }
        $this->inlineKeyboard[] = $formattedRow;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'inline_keyboard' => $this->inlineKeyboard
        ];
    }
}