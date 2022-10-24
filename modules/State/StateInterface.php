<?php

namespace LdH\State;

interface StateInterface
{
    public const TAG = 'ldh.state';

    public const STATE_END_ID   = 99;
    public const STATE_END_NAME = 'endGame';

    public const TYPE_GAME         = 'game';
    public const TYPE_ACTIVE       = 'activeplayer';
    public const TYPE_MULTI_ACTIVE = 'multipleactiveplayer';
    public const TYPE_PRIVATE      = 'private';

    /**
     * Get State ID
     *
     * @return int
     */
    public static function getId(): int;

    /**
     * Get State name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * The description is the string that is displayed in the main action bar (top of the screen) when the state is active.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get State type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * @param \APP_GameAction $gameAction
     *
     * @return callable[]|null
     */
    public function getActionCleanMethods(\APP_GameAction $gameAction): ?array;

    /**
     * @param \Table $game
     *
     * @return callable[]|null
     */
    public function getActionMethods(\Table $game): ?array;

    /**
     * @param \Table $game
     *
     * @return callable|null
     */
    public function getStateArgMethod(\Table $game): ?callable;

    /**
     * @param \Table $game
     *
     * @return callable|null
     */
    public function getStateActionMethod(\Table $game): ?callable;

    /**
     * Generate associative array used by BGA to build Game State Machine
     *
     * @return array
     */
    public function toArray(): array;
}
