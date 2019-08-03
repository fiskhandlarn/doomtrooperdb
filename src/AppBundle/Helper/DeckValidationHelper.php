<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Card;
use AppBundle\Model\ExportableDeck;
use AppBundle\Model\SlotCollectionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DeckValidationHelper
 * @package AppBundle\Helper
 */
class DeckValidationHelper
{
    /**
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * DeckValidationHelper constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ExportableDeck $deck
     * @return string|null
     */
    public function findProblem(ExportableDeck $deck)
    {
        $slots = $deck->getSlots();

        $plotDeck = $slots->getPlotDeck();
        $plotDeckSize = $plotDeck->countCards();

        /* @var integer $expectedPlotDeckSize Expected number of plots */
        $expectedPlotDeckSize = 7;
        $expectedMaxDoublePlot = 1;
        if ($plotDeckSize > $expectedPlotDeckSize) {
            return 'too_many_plots';
        }
        if ($plotDeckSize < $expectedPlotDeckSize) {
            return 'too_few_plots';
        }
        /* @var integer $expectedPlotDeckSpread Expected number of different plots */
        $expectedPlotDeckSpread = $expectedPlotDeckSize - $expectedMaxDoublePlot;
        if (count($plotDeck) < $expectedPlotDeckSpread) {
            return 'too_many_different_plots';
        }
        $expectedMinCardCount = 60;
        if ($slots->isAlliance()) {
            $expectedMinCardCount = 75;
        }
        if ($slots->getDrawDeck()->countCards() < $expectedMinCardCount) {
            return 'too_few_cards';
        }
        foreach ($slots->getCopiesAndDeckLimit() as $cardName => $value) {
            if ($value['copies'] > $value['deck_limit']) {
                return 'too_many_copies';
            }
        }
        if (!empty($this->getInvalidCards($deck))) {
            return 'invalid_cards';
        }

        return null;
    }

    /**
     * @param string|null $problem
     * @return string
     */
    public function getProblemLabel($problem): string
    {
        if (!$problem) {
            return '';
        }

        return $this->translator->trans('decks.problems.'.$problem);
    }

    /**
     * @param ExportableDeck $deck
     * @param Card $card
     * @return bool
     */
    public function canIncludeCard(ExportableDeck $deck, Card $card): bool
    {
        if ($card->getFaction()->getCode() === 'neutral') {
            return true;
        }
        if ($card->getFaction()->getCode() === $deck->getFaction()->getCode()) {
            return true;
        }
        if ($card->getIsLoyal()) {
            return false;
        }

        return false;
    }

    /**
     * @param ExportableDeck $deck
     * @return array
     */
    protected function getInvalidCards(ExportableDeck $deck): array
    {
        $invalidCards = [];
        foreach ($deck->getSlots() as $slot) {
            if (!$this->canIncludeCard($deck, $slot->getCard())) {
                $invalidCards[] = $slot->getCard();
            }
        }

        return $invalidCards;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateFealty(SlotCollectionInterface $slots): bool
    {
        $drawDeck = $slots->getDrawDeck();
        $count = 0;
        foreach ($drawDeck as $slot) {
            if ($slot->getCard()->getFaction()->getCode() === 'neutral') {
                $count += $slot->getQuantity();
            }
        }
        if ($count > 15) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateKings(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.'.(true ? 'winter' : 'summer'));
        $matchingTraitPlots = $slots->getPlotDeck()->filterByTrait($trait)->countCards();
        if ($matchingTraitPlots > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateRains(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.scheme');
        $matchingTraitPlots = $slots->getPlotDeck()->filterByTrait($trait);
        $matchingTraitPlotsUniqueCount = $matchingTraitPlots->count();
        $matchingTraitPlotsTotalCount = $matchingTraitPlots->countCards();
        if ($matchingTraitPlotsUniqueCount !== 5 || $matchingTraitPlotsTotalCount !== 5) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateAlliance(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.banner');

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateBrotherhood(SlotCollectionInterface $slots): bool
    {
        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            /* @var Card $card */
            $card = $slot->getCard();
            if ($card->getIsLoyal() && $card->getType()->getCode() === 'character') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateConclave(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.maester');
        $matchingMaesters = $slots->getDrawDeck()->filterByTrait($trait)->countCards();
        if ($matchingMaesters < 12) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateFreeFolk(SlotCollectionInterface $slots): bool
    {
        foreach ($slots->getPlotDeck()->getSlots() as $slot) {
            /* @var Card $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            /* @var Card $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        return true;
    }
}
