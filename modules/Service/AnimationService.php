<?php

namespace LdH\Service;

class AnimationService
{
    public const TYPE_UNIT_MOVE = 'moveUnit';
    public const TYPE_UNIT_ACT = 'actUnit';
    public const TYPE_UNIT_DIED = 'diedUnit';
    public const TYPE_UPDATE_CARTRIDGE = 'updateCartridge';
    public const TYPE_MOVE_TO_CARTRIDGE = 'moveResourceToCartridge';
    public const TYPE_CARD_FLIP = 'flipCard';
    public const TYPE_CARD_ACTIVATE = 'activateCard';
    public const TYPE_CARD_DISABLE = 'disableCard';
    public const TYPE_CARD_DRAW = 'drawCard';

    public static function buildAnimation(string $type, string $subject, string $target, ?int $duration = 0): array
    {
        return [
            'type' => self::validateType($type),
            'subject' => $subject,
            'target' => $target,
            'duration' => $duration
        ];
    }

    protected static function validateType(string $type): string
    {
        switch ($type) {
        case self::TYPE_CARD_ACTIVATE:
        case self::TYPE_CARD_FLIP:
        case self::TYPE_CARD_DRAW:
        case self::TYPE_CARD_DISABLE:
        case self::TYPE_UNIT_ACT:
        case self::TYPE_UNIT_DIED:
        case self::TYPE_MOVE_TO_CARTRIDGE:
        case self::TYPE_UNIT_MOVE:
        case self::TYPE_UPDATE_CARTRIDGE:
            return $type;
        default:
            return self::TYPE_CARD_DRAW;
        }
    }
}