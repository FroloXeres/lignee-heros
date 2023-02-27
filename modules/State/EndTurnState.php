<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\PeopleService;
use LdH\Service\CurrentStateService;

class EndTurnState extends AbstractState
{
    public const ID = 7;
    public const NAME = 'EndPrincipal';

    public const TR_END = 'trEnd';
    public const TR_NEXT_TURN = 'trNexTurn';

    public const NOTIFY_END_TURN = 'ntfyEndTurn';
    public const NOTIFY_SCIENCE_HARVEST = 'ntfyScienceHarvest';
    public const NOTIFY_FOOD_HARVEST = 'ntfyFoodHarvest';
    public const NOTIFY_START_TURN = 'ntfyStartTurn';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Start harvest phase");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_NEXT_TURN => PrincipalState::ID,
            self::TR_END       => DeadEndState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turn = $this->getCurrentTurn((int) $this->getGameStateValue(CurrentStateService::GLB_TURN_LFT));
            $args = [
                'turn' => $turn,
                'endOfTurn' => clienttranslate('End of turn '.$turn),
                'newTurn' => clienttranslate('Start of turn '.($turn + 1)),
            ];
            return $args;
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turnLeft = (int) $this->getGameStateValue(CurrentStateService::GLB_TURN_LFT);
            $turn = $this->getCurrentTurn($turnLeft);

            if ($turnLeft > 1) {
                $this->incGameStateValue(CurrentStateService::GLB_TURN_LFT, -1);
                $this->notifyAllPlayers(EndTurnState::NOTIFY_END_TURN, clienttranslate('End of turn '.$turn), []);

                EndTurnState::endTurn($this);

                $notificationParams = ['turn' => ++$turn];
                $this->notifyAllPlayers(EndTurnState::NOTIFY_START_TURN, clienttranslate('Start of turn '.$turn), $notificationParams);

                $this->gamestate->setAllPlayersMultiactive();
                $this->gamestate->nextState(EndTurnState::TR_NEXT_TURN);
                return ;
            }

            // Last turn
            $this->gamestate->nextState(EndTurnState::TR_END);
        };
    }

    public static function endTurn(\ligneeheros $game): void
    {
        $unitRepository = $game->getCardService()->getCardRepository(Unit::class);

        // Science harvest
        $scienceHarvest = (int) $game->getGameStateValue(CurrentStateService::GLB_SCIENCE_PRD);
        $scienceHarvesters = $unitRepository->getScienceHarvesters(
            $game->mapService->getScienceHarvestCodes($game->terrains)
        );

        $scienceTotalHarvesters = array_sum($scienceHarvesters);
        $scienceToAdd = $scienceHarvest * $scienceTotalHarvesters;
        if ($scienceToAdd) {
            $game->incGameStateValue(CurrentStateService::GLB_SCIENCE, $scienceToAdd);
            $game->notifyAllPlayers(
                EndTurnState::NOTIFY_SCIENCE_HARVEST,
                clienttranslate('[savant] harvest '.$scienceToAdd . ' [science]'),
                [
                    'harvesters' => $scienceHarvesters,
                    'count' => $scienceToAdd,
                ]
            );
        }

        // Food harvest
        $foodHarvest = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD_PRD);
        $foodHarvesters = $unitRepository->getFoodHarvesters(
            $game->mapService->getScienceHarvestCodes($game->terrains)
        );


        // Inventions

        // Resources renew

        // Feed people


        // People are available

    }
}
