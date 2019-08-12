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
}
