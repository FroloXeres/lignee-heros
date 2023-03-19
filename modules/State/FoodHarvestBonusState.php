<?php

namespace LdH\State;

class FoodHarvestBonusState extends AbstractState
{
    public const ID = 10;
    public const NAME = 'FoodHarvestBonus';

    public const ACTION_PASS = 'fhBonusPass';

    public const TR_PASS = 'trPass';
    public const TR_INVENTION = 'trInvention';
    public const TR_SPELL = 'trSpell';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate("Use invention or spell to increase food harvest");
        $this->descriptionMyTurn = clienttranslate("Use invention or spell to increase food harvest");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [
            self::ACTION_PASS,
        ];
        $this->transitions       = [
            self::TR_PASS => FoodHarvestState::ID,
            // Add Invention activation
            // Add Spell activation
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            return [];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            // Check if some spell or invention can be activated to increase science produced
            $canUseInvention = false;
            $canUseSpell = false;
            // $playerId = (int) $this->getCurrentPlayerId();

            if ($canUseInvention || $canUseSpell) {
                $this->gamestate->setAllPlayersMultiactive();
            } else {
                // No need to ask players
                $this->gamestate->nextState(FoodHarvestBonusState::TR_PASS);
            }
        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{FoodHarvestBonusState::ACTION_PASS}();
            },
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \ligneeheros $this */

                $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayerId(), FoodHarvestBonusState::TR_PASS);
            },
        ];
    }
}
