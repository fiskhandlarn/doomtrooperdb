<?php

namespace AppBundle\Classes;

use AppBundle\Entity\Decklistslot;
use Doctrine\Common\Collections\Collection;

/**
 * Checks if a given list of cards is legal for tournament play.
 * @package AppBundle\Classes
 */
class RestrictedListChecker
{
    /**
     * @param array $cardCodes
     * @param array $restrictedList
     * @return bool
     */
    protected function isLegal(array $cardCodes, array $restrictedList)
    {
        $intersection = array_intersect($cardCodes, $restrictedList);
        return 2 > count($intersection);
    }
}
