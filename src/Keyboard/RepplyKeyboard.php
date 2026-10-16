<?php

namespace TeleCore\Keyboard;

class ReplyKeyboard
{
    protected array $keyboard = [];
    protected bool $resizeKeyboard = true;
    protected bool $oneTimeKeyboard = false;
    protected bool $selective = false;
    protected ?string $placeholder = null;

    /**
     * Add inline button on the repplies
     * 
     * @param array $buttons List of button text or a custom configuration array
     */
    public function row(array $buttons): self
    {
        $formattedRow = [];
        foreach ($buttons as $button) {
            if (is_string($button)) {
                $formattedRow[] = ['text' => $button];
            } elseif (is_array($button)) {
                $formattedRow[] = $button;
            }
        }
        $this->keyboard[] = $formattedRow;
        return $this;
    }

    /**
     * Controls whether the keyboard size is automatically adjusted (reduced)
     */
    public function resize(bool $resize = true): self
    {
        $this->resizeKeyboard = $resize;
        return $this;
    }

    /**
     * Controls whether the keyboard is automatically hidden after a key is pressed
     */
    public function oneTime(bool $oneTime = true): self
    {
        $this->oneTimeKeyboard = $oneTime;
        return $this;
    }

    /**
     * Display placeholder text in the chat input field when the keyboard appears
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Converting the builder structure into a payload array ready to be sent to the Telegram API
     */
    public function toArray(): array
    {
        $result = [
            'keyboard' => $this->keyboard,
            'resize_keyboard' => $this->resizeKeyboard,
            'one_time_keyboard' => $this->oneTimeKeyboard,
            'selective' => $this->selective,
        ];

        if ($this->placeholder !== null) {
            $result['input_field_placeholder'] = $this->placeholder;
        }

        return $result;
    }
}