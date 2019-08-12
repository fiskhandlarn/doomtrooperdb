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

        $expectedMinCardCount = 60;
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
        if ($card->getFaction()->getCode() === $deck->getFaction()->getCode()) {
            return true;
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
}
