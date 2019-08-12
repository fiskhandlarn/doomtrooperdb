<?php

namespace AppBundle\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for a collection of SlotInterface
 */
interface SlotCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Adds a slot to the collection.
     * @param SlotInterface $slot
     */
    public function add(SlotInterface $slot);

    /**
     * Removes a slot from the collection.
     * @param SlotInterface $slot
     */
    public function removeElement(SlotInterface $slot);
    /**
     * Get quantity of cards
     * @return integer
     */
    public function countCards();

    /**
     * Get included expansions
     * @return \AppBundle\Entity\Expansion[]
     */
    public function getIncludedExpansions();

    /**
     * Get all slots sorted by type code
     * @return array
     */
    public function getSlotsByType();

    /**
     * Get all slots sorted by expansion number
     * @return array
     */
    public function getSlotsByExpansionOrder();

    /**
     * Get all slot counts sorted by type code
     * @return array
     */
    public function getCountByType();

    /**
     * Get the draw deck
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getDrawDeck();

    /**
     * Get the content as an array card_code => qty
     * @return array
     */
    public function getContent();

    /**
     *
     * @param string $faction_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByFaction($faction_code);

    /**
     *
     * @param string $type_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByType($type_code);

    /**
     * Returns the collection of slots.
     * @return Collection
     */
    public function getSlots();

    /**
     * Returns a map of limits and total copies per card in the collection, keyed off by card name.
     * @return array
     */
    public function getCopiesAndDeckLimit();
}
