<?php

namespace LdH\Service;

use LdH\State\StateInterface;

class StateService
{
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
     * @param \Table $game
     *
     * @return callable[]
     */
    public function getActionMethods(\Table $game): array
    {
        $callables = [];

        foreach ($this->states as $state) {
            foreach ($state->getActionMethods($game) as $methodName => $actionMethod) {
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
