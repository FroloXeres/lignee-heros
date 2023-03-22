<?php

namespace LdH\State;

class ScienceHarvestBonusState extends AbstractState
{
    public const ID = 11;
    public const NAME = 'ScienceHarvestBonus';

    public const ACTION_PASS = 'shBonusPass';

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
        $this->description       = clienttranslate("Use invention or spell to increase science harvest");
        $this->descriptionMyTurn = clienttranslate("Use invention or spell to increase science harvest");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [
            self::ACTION_PASS,
        ];
        $this->transitions       = [
            self::TR_PASS => ScienceHarvestState::ID,
            // Add Invention activation
            // Add Spell activation
        ];
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
                $this->gamestate->nextState(ScienceHarvestBonusState::TR_PASS);
            }
        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{ScienceHarvestBonusState::ACTION_PASS}();
            },
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \ligneeheros $this */


                $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayerId(), ScienceHarvestBonusState::TR_PASS);
            },
        ];
    }
}
