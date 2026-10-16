<?php

namespace TeleCore\Conversation;

use TeleCore\Bot;
use TeleCore\Context;

class ConversationManager
{
    protected Bot $bot;
    protected string $conversationName;
    /** @var Step[] */
    protected array $steps = [];
    protected int $timeoutSeconds = 300;

    public function __construct(Bot $bot, string $conversationName)
    {
        $this->bot = $bot;
        $this->conversationName = $conversationName;
    }

    public function addStep(Step $step): self
    {
        $this->steps[$step->getName()] = $step;
        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeoutSeconds = $seconds;
        return $this;
    }

    public function start(Context $context): void
    {
        $userId = $context->getUserId();
        $storage = $this->bot->getStorage();

        if (!$storage) {
            throw new \RuntimeException("Conversation membutuhkan Storage Driver yang aktif di Bot.");
        }

        if (empty($this->steps)) {
            throw new \RuntimeException("Conversation tidak memiliki langkah (steps) yang terdaftar.");
        }

        $firstStepName = array_key_first($this->steps);
        
        $session = [
            'conversation' => $this->conversationName,
            'current_step' => $firstStepName,
            'data' => [],
            'last_activity' => time()
        ];

        $storage->set($this->getStorageKey($userId), $session, $this->timeoutSeconds);

        $this->steps[$firstStepName]->executeEnter($context, $session['data']);
    }

    public function handle(Context $context): bool
    {
        $userId = $context->getUserId();
        $storage = $this->bot->getStorage();

        if (!$userId || !$storage) {
            return false;
        }

        $sessionKey = $this->getStorageKey($userId);
        $session = $storage->get($sessionKey);

        if (!$session || !isset($session['conversation']) || $session['conversation'] !== $this->conversationName) {
            return false; 
        }

        if (time() - $session['last_activity'] > $this->timeoutSeconds) {
            $storage->delete($sessionKey);
            $context->reply("⏳ Sesi percakapan telah kedaluwarsa karena tidak ada aktivitas.");
            return true;
        }

        $currentStepName = $session['current_step'];
        if (!isset($this->steps[$currentStepName])) {
            $storage->delete($sessionKey);
            return false;
        }

        $step = $this->steps[$currentStepName];

        if (!$step->validate($context, $session['data'])) {
            $step->executeFail($context, $session['data']);
            return true; // Cegah router lain merespons saat input salah
        }

        $stepKeys = array_keys($this->steps);
        $currentIndex = array_search($currentStepName, $stepKeys);

        if ($currentIndex !== false && isset($stepKeys[$currentIndex + 1])) {
            $nextStepName = $stepKeys[$currentIndex + 1];
            $session['current_step'] = $nextStepName;
            $session['last_activity'] = time();

            $storage->set($sessionKey, $session, $this->timeoutSeconds);

            $this->steps[$nextStepName]->executeEnter($context, $session['data']);
        } else {
            $storage->delete($sessionKey);
            $context->reply("✅ Selesai! Terima kasih telah melengkapi data.");
        }

        return true;
    }

    protected function getStorageKey(int $userId): string
    {
        return "telecore_conv_{$userId}";
    }
}