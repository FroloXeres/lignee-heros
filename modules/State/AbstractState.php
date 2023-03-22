<?php

namespace LdH\State;

abstract class AbstractState implements StateInterface
{
    /**
     * The name of a game state is used to identify it in your game logic.
     * Several game states can share the same name; however, this is not recommended.
     *
     * @var string
     */
    protected string $name;

    /**
     * One of State TYPE defined in Interface
     *
     * @var string
     */
    protected string $type;

    /**
     * The description is the string that is displayed in the main action bar (top of the screen) when the state is active.
     *
     * @var string
     */
    protected string $description;

    /**
     * Has exactly the same role and properties as "description", except that this value is displayed to the current active player -
     * or to all active players in case of a multipleactiveplayer game state.
     *
     * @var string
     */
    protected string $descriptionMyTurn;

    /**
     * Specifies a PHP method to call when entering this game state.
     *
     * @var string
     */
    protected string $action;

    /**
     * Specify which game state(s) you can jump to from a given game state.
     *
     * @var array
     */
    protected array  $transitions;

    /**
     * Defines the actions possible by the players in this game state, and ensures they cannot perform actions that are not allowed in this state.
     *
     * @var string[]
     */
    protected array  $possibleActions = [];

    /**
     * Game logic method name to send elements to Client Side for this state only
     *
     * @var string
     */
    protected ?string $args = null;

    /**
     * Do we need to call 'getGameProgression' at the beginning of this game state (At least, one state as to call)
     *
     * @var bool
     */
    protected bool $updateGameProgression = false;

    /**
     * This parameter will enable private parallel states in a multiplayer state. Parameter should be set to first private parallel state a player will be transitioned to.
     *
     * @var int|null
     */
    protected ?int $initialPrivate;

    /**
     * Get State ID
     *
     * @return int
     */
    abstract public static function getId(): int;

    public function getName(): string {return $this->name;}
    public function getType(): string {return $this->type;}
    public function getDescription(): string {return $this->description;}

    /**
     * @return array<string, callable>|null
     */
    public function getActionCleanMethods(): array
    {
        return [];
    }

    /**
     * @return array<string, callable>|null
     */
    public function getActionMethods(): array
    {
        return [];
    }

    /**
     * @return callable[]|null
     */
    public function getStateArgMethod(): ?callable
    {
        return null;
    }

    /**
     * @return callable[]|null
     */
    abstract public function getStateActionMethod(): ?callable;

    /**
     * Generate associative array used by BGA to build Game State Machine
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toArray(): array
    {
        $stateAsArray = [
            'name'                  => $this->name,
            "type"                  => $this->type,
            "description"           => $this->description,
            "action"                => $this->action?? null,
            "updateGameProgression" => $this->updateGameProgression,
            "transitions"           => $this->transitions
        ];

        if (in_array($this->getType(), [self::TYPE_ACTIVE, self::TYPE_MULTI_ACTIVE])) {
            $stateAsArray['descriptionmyturn'] = $this->descriptionMyTurn;

            if (is_callable($this->getStateArgMethod())) {
                $stateAsArray['args'] = 'callArgMethod';
            }

            if (empty($this->possibleActions)) {
                throw new \Exception(sprintf('possibleActions as to be set for state %s of type %s', $this->name, $this->type));
            }
        }

        if (!empty($this->possibleActions)) {
            $stateAsArray['possibleactions'] = $this->possibleActions;
        }

        return $stateAsArray;
    }
}
