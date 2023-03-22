<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Unit;
use LdH\Service\CurrentStateService;
use LdH\Service\PeopleService;


class EndOfEndTurnState extends AbstractState
{
    public const ID = 12;
    public const NAME = 'EndOfEndTurn';

    public const TR_PRINCIPAL = 'trPrincipal';

    public const NOTIFY_DISABLED_CARDS = 'ntfyDisabledCards';
    public const NOTIFY_DIED_PEOPLE = 'ntfyDiedPeople';
    public const NOTIFY_FOOD_STOCK = 'ntfyFoodStock';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Inventions effect and People feeding");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_PRINCIPAL => PrincipalState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            return [

            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            // Apply end of turn inventions effects


            //  Resource tokens consumed
            // For animation purpose


            // Population feed (maybe dead people)
            EndOfEndTurnState::feedPeople($this, $this->getPeople());

            // Inventions/spells are no more activated
            $disabled = $this->getCardService()->disableCards([
                $this->cards[AbstractCard::TYPE_INVENTION],
                $this->cards[AbstractCard::TYPE_MAGIC],
            ]);
            $this->notifyAllPlayers(EndOfEndTurnState::NOTIFY_DISABLED_CARDS, clienttranslate('[invention] cards are available again'), [
                'disabled' => $disabled,
            ]);

            // Units are free
            $this->getPeople()->freeUnits();

            // Next turn
            $this->incGameStateValue(CurrentStateService::GLB_TURN_LFT, -1);
            $this->gamestate->nextState(EndOfEndTurnState::TR_PRINCIPAL);
        };
    }

    public static function feedPeople(\ligneeheros $game, PeopleService $peopleService): void
    {
        $food = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD);
        $foodStock = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD_STK);
        $population = $peopleService->getPopulation();

        $died = [];
        if ($food < $population) {
            // Choose people who will die...
            $notFeedPeople = $population - $food;
            $units = $peopleService->getByTypeUnits();
            foreach (Unit::NOT_FEED_ORDER as $unitType) {
                $units = $units[$unitType];
                $unitCount = count($units);
                $diedCount = min($unitCount, $notFeedPeople);

                for ($i = 0; $i < $diedCount; $i++) {
                    $unit = $units[$i];
                    $died[] = $unit->getId();
                    $game->incGameStateValue(CurrentStateService::GLB_PEOPLE_CNT, -1);
                    $game->incGameStateValue(CurrentStateService::getStateByMeepleType($unit->getType()), -1);
                    $peopleService->kill($unit);
                }

                $notFeedPeople -= $diedCount;
                if (!$notFeedPeople) break;
            }
        }

        if (!empty($died)) {
            $game->notifyAllPlayers(EndOfEndTurnState::NOTIFY_DIED_PEOPLE, clienttranslate('${count} people has died because of lack of [food]'), [
                'i18n' => ['count'],
                'count' => count($died),
                'died' => $died
            ]);
        }

        // Not stocked food is lost
        $foodResidual = min($foodStock, max(0, $food - $population));
        $game->setGameStateValue(CurrentStateService::GLB_FOOD, $foodResidual);
        $game->notifyAllPlayers(EndOfEndTurnState::NOTIFY_FOOD_STOCK, clienttranslate('After people feed, it stays ${food} [food]'), [
            'i18n' => ['food'],
            'food' => $foodResidual,
            'foodUpdate' => $foodResidual - $food,
        ]);
    }
}
