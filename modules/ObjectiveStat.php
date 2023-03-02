<?php

namespace LdH;

class ObjectiveStat
{
    protected array $byKey = [];
    protected array $byPlayer = [];

    public function __construct(
        array $completed = []
    ) {
        $this->init($completed);
    }

    protected function init(array $completed)
    {
        foreach ($completed as $objective) {
            $playerId = (int) $objective['playerId'];
            $objectiveId = (int) $objective['objectiveType'];
            if (!array_key_exists($playerId, $this->byPlayer)) {
                $this->byPlayer[$playerId] = [];
            }

            $this->byPlayer[$playerId][] = $objectiveId;
            $this->byKey[$objectiveId] = true;
        }
    }

    public function getPlayerObjectiveCount(int $playerId): int
    {
        return count($this->byPlayer[$playerId] ?? []);
    }

    public function isObjectiveCompleted(int $objectiveKey): bool
    {
        return array_key_exists($objectiveKey, $this->byKey);
    }
}