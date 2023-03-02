<?php

namespace LdH\State;

use LdH\Entity\Bonus;
use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Repository\AbstractCardRepository;
use LdH\Repository\CardRepository;
use LdH\Service\BonusService;
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
            $turn = $this->getCurrentTurn();
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
            $turn = $this->getCurrentTurn();

            if ($turn < CurrentStateService::LAST_TURN) {
                $this->incGameStateValue(CurrentStateService::GLB_TURN_LFT, -1);
                $this->notifyAllPlayers(EndTurnState::NOTIFY_END_TURN, clienttranslate('[end_turn] phase'), []);

                EndTurnState::endTurn($this);

                $this->notifyAllPlayers(EndTurnState::NOTIFY_START_TURN, clienttranslate('[turn] <b>${turn}</b> started'), ['i18n' => ['turn'], 'turn' => ++$turn]);

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
        $bonusService = new BonusService($game);
        $unitRepository = $game->getCardService()->getCardRepository(Unit::class);

        // Activate people


        // Science harvest
        self::scienceHarvest($game, $unitRepository, $bonusService);

        // Food harvest
        self::foodHarvest($game, $unitRepository, $bonusService);

        // Inventions


        // Invention activated finished (Change bonuses)


        // Resources renew

        // Feed people


        // People are available

    }

    public static function scienceHarvest(\ligneeheros $game, CardRepository $unitRepository, BonusService $bonusService): void
    {
        $population = (int) $game->getGameStateValue(CurrentStateService::GLB_PEOPLE_CNT);
        $scienceHarvest = (int) $game->getGameStateValue(CurrentStateService::GLB_SCIENCE_PRD);

        $scienceHarvestersCount = $unitRepository->getScienceHarvestersCount($game->mapService->getScienceHarvestCodes($game->terrains));
        $scienceTotalHarvesters = array_sum($scienceHarvestersCount);
        $scienceToAdd =
            $scienceHarvest * $scienceTotalHarvesters
            + floor($population / 5)
            + $bonusService->getHarvestScienceBonus()
        ;

        if ($scienceToAdd) {
            $game->incGameStateValue(CurrentStateService::GLB_SCIENCE, $scienceToAdd);
            $game->notifyAllPlayers(
                EndTurnState::NOTIFY_SCIENCE_HARVEST,
                clienttranslate('${count} [science] harvested'),
                [
                    'i18n' => ['count'],
                    'harvesters' => $scienceHarvestersCount,
                    'count' => $scienceToAdd,
                ]
            );
        }
    }

    public static function foodHarvest(\ligneeheros $game, CardRepository $unitRepository, BonusService $bonusService): void
    {
        $foodHarvest = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD_PRD);
        $foodHarvesters = $unitRepository->getFoodHarvesters(
            $game->mapService->getFoodHarvestCount($game->terrains)
        );


    }
}
