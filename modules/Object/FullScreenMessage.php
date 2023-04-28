<?php

namespace LdH\Object;

class FullScreenMessage implements \JsonSerializable
{
    public const DEFAULT_DURATION = 3000;

    public string $message;
    public int $duration;
    public bool $translate;

    public function __construct(
        string $message,
        int $duration = self::DEFAULT_DURATION,
        bool $translate = true
    ) {
        $this->message = $message;
        $this->duration = $duration;
        $this->translate = $translate;
    }

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->translate ? clienttranslate($this->message) : $this->message,
            'duration' => $this->duration,
            'translate' => $this->translate
        ];
    }
}