<?php

namespace LdH\Service;

use LdH\State\StateInterface;

class StateService
{
    public const GLB_TURN_LFT    = 'turnLeft';
    public const GLB_PEOPLE_CNT  = 'peopleCount';
    public const GLB_FOOD_PRD    = 'foodProduction';
    public const GLB_FOOD_STK    = 'foodStock';
    public const GLB_SCIENCE_PRD = 'scienceProduction';
    public const GLB_SCIENCE_STK = 'scienceStock';
    public const GLB_LIFE        = 'life';
    public const GLB_WAR_PWR     = 'warriorPower';
    public const GLB_WAR_DFS     = 'warriorDefense';
    public const GLB_CTY_DFS     = 'cityDefense';

    /**
     * @var StateInterface[]
     */
    protected array $states = [];

    /**
     * @param StateInterface $state
     *
     * @throws \Exception
     */
    public function addState(StateInterface $state): void
    {
        if (!array_key_exists($state::getId(), $this->states)) {
            $this->states[$state::getId()] = $state;
        } else {
            throw new \Exception(sprintf(
                'Try to add existing state ID %s [%s]',
                $state::getId(),
                $state->getName()
            ));
        }
    }

    /**
     * @param \Table $game
     *
     * @return callable[]
     */
    public function getStateArgMethods(\Table $game): array
    {
        $callables = [];

        foreach ($this->states as $state) {
            $callables['arg' . ucfirst($state->getName())] = $state->getStateArgMethod($game);
        }

        return  $callables;
    }

    /**
     * @param \Table $game
     *
     * @return callable[]
     */
    public function getStateActionMethods(\Table $game): array
    {
        $callables = [];

        foreach ($this->states as $state) {
            $callables['st' . ucfirst($state->getName())] = $state->getStateArgMethod($game);
        }

        return  $callables;
    }

    /**
     * @param \APP_GameAction $gameAction
     *
     * @return callable[]
     */
    public function getActionMethods(\APP_GameAction $gameAction): array
    {
        $callables = [];

        foreach ($this->states as $state) {
            foreach ($state->getActionMethods($gameAction) as $methodName => $actionMethod) {
                $callables[$methodName] = $actionMethod;
            }
        }

        return  $callables;
    }

    /**
     * @param array $stateMachine
     *
     * @return void
     */
    public function updateStateMachine(array &$stateMachine): void
    {
        foreach ($this->states as $state) {
            $stateMachine[$state::getId()] = $state->toArray();
        }
    }
}
