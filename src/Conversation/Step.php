<?php

namespace TeleCore\Conversation;

use TeleCore\Context;

class Step
{
    protected string $name;
    /** @var callable */
    protected $onEnter;
    /** @var callable|null */
    protected $validator;
    /** @var callable|null */
    protected $onFail;

    public function __construct(string $name, callable $onEnter, ?callable $validator = null, ?callable $onFail = null)
    {
        $this->name = $name;
        $this->onEnter = $onEnter;
        $this->validator = $validator;
        $this->onFail = $onFail;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function executeEnter(Context $context, array &$sessionData): mixed
    {
        $handler = $this->onEnter;
        return $handler($context, $sessionData);
    }

    public function validate(Context $context, array &$sessionData): bool
    {
        if ($this->validator === null) {
            return true;
        }
        $validator = $this->validator;
        return $validator($context, $sessionData);
    }

    public function executeFail(Context $context, array &$sessionData): mixed
    {
        if ($this->onFail === null) {
            return $context->reply("❌ <b>Input tidak valid!</b> Silakan coba lagi sesuai format yang diminta.");
        }
        $failHandler = $this->onFail;
        return $failHandler($context, $sessionData);
    }
}