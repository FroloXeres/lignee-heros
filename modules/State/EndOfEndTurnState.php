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
    public const TR_END = 'trAllDied';

    public const NOTIFY_RENEW_RESOURCES = 'ntfyRenewResources';
    public const NOTIFY_DISABLED_CARDS = 'ntfyDisabledCards';
    public const NOTIFY_PEOPLE_FREE = 'ntfyPeopleFree';
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
        $this->transitions       = [
            self::TR_PRINCIPAL => PrincipalState::ID,
            self::TR_END => AllDiedState::ID,
        ];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            // todo: Apply end of turn inventions effects


            //  Resource tokens regenerated
            $this->getMapService()->renewResources();
            $this->notifyAllPlayers(EndOfEndTurnState::NOTIFY_RENEW_RESOURCES, clienttranslate('Harvested resources are available again'), [
                'map' => $this->getPeople()->getHarvestableResources($this->terrains),
            ]);

            // Population feed (maybe dead people, Maybe all!)
            if (!EndOfEndTurnState::feedPeople($this, $this->getPeople())) return ;

            // Inventions/spells are no more activated
            $disabled = $this->getCardService()->disableCards([
                $this->cards[AbstractCard::TYPE_INVENTION],
                $this->cards[AbstractCard::TYPE_MAGIC],
            ]);
            $this->notifyAllPlayers(EndOfEndTurnState::NOTIFY_DISABLED_CARDS, clienttranslate('[invention] cards are available again'), [
                'disabled' => $disabled,
            ]);

            // Units are free
            $units = $this->getPeople()->freeUnits();
            $this->notifyAllPlayers(EndOfEndTurnState::NOTIFY_PEOPLE_FREE, clienttranslate('All units can be used again'), [
                'units' => $units,
            ]);

            // Can master a spell
            $this->setGameStateValue(CurrentStateService::GLB_SPELL_MASTERED, false);

            // Next turn
            $this->incGameStateValue(CurrentStateService::GLB_TURN_LFT, -1);
            $this->gamestate->nextState(EndOfEndTurnState::TR_PRINCIPAL);
        };
    }

    public static function feedPeople(\ligneeheros $game, PeopleService $peopleService): bool
    {
        $food = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD);
        $foodStock = (int) $game->getGameStateValue(CurrentStateService::GLB_FOOD_STK);
        $population = $peopleService->getPopulation();

        $died = [];
        $diedCount = 0;
        $cartridge = ['count' => []];
        if ($food < $population) {
            // Choose people who will die...
            $notFeedPeople = $population - $food;
            $byTypeUnits = $peopleService->getByTypeUnits();
            foreach (Unit::NOT_FEED_ORDER as $unitType) {
                $units = $byTypeUnits[$unitType] ?? [];
                $unitCount = count($units);
                $diedCount = min($unitCount, $notFeedPeople);

                for ($i = 0; $i < $diedCount; $i++) {
                    $unit = $units[$i];
                    $died[] = $unit->getId();
                    $cartridge['count'][CurrentStateService::GLB_PEOPLE_CNT] = $game->incGameStateValue(CurrentStateService::GLB_PEOPLE_CNT, -1);

                    $peopleType = CurrentStateService::getStateByMeepleType($unit->getType());
                    $cartridge['count'][$peopleType] = $game->incGameStateValue($peopleType, -1);

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
                'died' => $died,
                'cartridge' => $cartridge,
            ]);
        }
        if ($diedCount === $population) {
            $game->gamestate->nextState(EndOfEndTurnState::TR_END);
            return false;
        }

        // Not stocked food is lost
        $foodResidual = min($foodStock, max(0, $food - $population));
        $game->setGameStateValue(CurrentStateService::GLB_FOOD, $foodResidual);
        $game->notifyAllPlayers(EndOfEndTurnState::NOTIFY_FOOD_STOCK, clienttranslate('After people feed, it stays ${food} [food]'), [
            'i18n' => ['food'],
            'food' => $foodResidual,
            'cartridge' => CurrentStateService::getCartridgeUpdate(CurrentStateService::GLB_FOOD, $foodResidual),
        ]);

        return true;
    }
}
