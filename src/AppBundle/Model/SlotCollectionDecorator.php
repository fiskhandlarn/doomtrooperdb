<?php

namespace AppBundle\Model;

use AppBundle\Classes\RestrictedListChecker;
use AppBundle\Entity\Decklistslot;
use AppBundle\Entity\Expansion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Decorator for a collection of slots.
 */
class SlotCollectionDecorator implements SlotCollectionInterface
{
    /**
     * @var Collection
     */
    protected $slots;

    /**
     * @var RestrictedListChecker
     */
    protected $restrictedListChecker;

    /**
     * SlotCollectionDecorator constructor.
     * @param Collection $slots
     */
    public function __construct(Collection $slots)
    {
        $this->slots = $slots;
        $this->restrictedListChecker = new RestrictedListChecker();
    }

    /**
     * @inheritdoc
     */
    public function add(SlotInterface $slot)
    {
        return $this->slots->add($slot);
    }

    /**
     * @inheritdoc
     */
    public function removeElement(SlotInterface $slot)
    {
        return $this->slots->removeElement($slot);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->slots->count();
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->slots->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->slots->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->slots->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->slots->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->slots->offsetUnset($offset);
    }

    /**
     * @inheritdoc
     */
    public function countCards()
    {
        $count = 0;
        foreach ($this->slots as $slot) {
            $count += $slot->getQuantity();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedExpansions()
    {
        $expansions = [];
        /** @var SlotInterface $slot */
        foreach ($this->slots as $slot) {
            $card = $slot->getCard();
            $expansion = $card->getExpansion();
            if (!isset($expansions[$expansion->getId()])) {
                $expansions[$expansion->getId()] = [
                    'expansion' => $expansion,
                    'nb' => 0,
                ];
            }

            $nbexpansions = ceil($slot->getQuantity() / $card->getQuantity());
            if ($expansions[$expansion->getId()]['nb'] < $nbexpansions) {
                $expansions[$expansion->getId()]['nb'] = $nbexpansions;
            }
        }

        $expansions =  array_values($expansions);
        usort($expansions, function ($arr1, $arr2) {
            /** @var Expansion $expansion1 */
            $expansion1 = $arr1['expansion'];
            /** @var Expansion $expansion2 */
            $expansion2 = $arr2['expansion'];
            $expansion1 = $expansion1->getExpansion();
            $expansion2 = $expansion2->getExpansion();
            if ($expansion1->getPosition() > $expansion2->getPosition()) {
                return 1;
            } elseif ($expansion1->getPosition() < $expansion2->getPosition()) {
                return -1;
            }

            if ($expansion1->getPosition() > $expansion2->getPosition()) {
                return 1;
            } elseif ($expansion1->getPosition() < $expansion2->getPosition()) {
                return -1;
            }
            return 0;
        });
        return $expansions;
    }

    /**
     * @inheritdoc
     */
    public function getSlotsByType()
    {
        $slotsByType = [
            'alliance' => [],
            'art' => [],
            'beast' => [],
            'symmetry' => [],
            'equipment' => [],
            'fortification' => [],
            'ki' => [],
            'mission' => [],
            'relic' => [],
            'special' => [],
            'warrior' => [],
            'warzone' => [],
        ];
        foreach ($this->slots as $slot) {
            if (array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
                $slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
            }
        }
        foreach ($slotsByType as &$slots) {
            usort($slots, array($this, 'sortByCardName'));
        }
        return $slotsByType;
    }

    /**
     * @inheritdoc
     */
    public function getSlotsByExpansionOrder()
    {
        $slots_array = [];
        foreach ($this->slots as $slot) {
            $slots_array[] = $slot;
        }

        usort($slots_array, array($this, "sortByCardCode"));
        $expansions = [];
        foreach ($slots_array as $slot) {
            $expansions[$slot->getCard()->getExpansion()->getName()][] = $slot;
        }
        return $expansions;
    }

    /**
     * @inheritdoc
     */
    public function getCountByType()
    {
        $countByType = [
            'alliance' => 0,
            'art' => 0,
            'beast' => 0,
            'symmetry' => 0,
            'equipment' => 0,
            'fortification' => 0,
            'ki' => 0,
            'mission' => 0,
            'relic' => 0,
            'special' => 0,
            'warrior' => 0,
            'warzone' => 0,
        ];
        foreach ($this->slots as $slot) {
            if (array_key_exists($slot->getCard()->getType()->getCode(), $countByType)) {
                $countByType[$slot->getCard()->getType()->getCode()] += $slot->getQuantity();
            }
        }
        return $countByType;
    }

    /**
     * @inheritdoc
     */
    public function getDrawDeck()
    {
        $drawDeck = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === 'alliance'
                || $slot->getCard()->getType()->getCode() === 'art'
                || $slot->getCard()->getType()->getCode() === 'beast'
                || $slot->getCard()->getType()->getCode() === 'symmetry'
                || $slot->getCard()->getType()->getCode() === 'equipment'
                || $slot->getCard()->getType()->getCode() === 'fortification'
                || $slot->getCard()->getType()->getCode() === 'ki'
                || $slot->getCard()->getType()->getCode() === 'mission'
                || $slot->getCard()->getType()->getCode() === 'relic'
                || $slot->getCard()->getType()->getCode() === 'special'
                || $slot->getCard()->getType()->getCode() === 'warrior'
                || $slot->getCard()->getType()->getCode() === 'warzone') {
                $drawDeck[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($drawDeck));
    }

    /**
     * @inheritdoc
     */
    public function filterByFaction($faction_code)
    {
        $slots = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getFaction()->getCode() === $faction_code) {
                $slots[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($slots));
    }

    /**
     * @inheritdoc
     */
    public function filterByType($type_code)
    {
        $slots = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === $type_code) {
                $slots[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($slots));
    }

    /**
     * @inheritdoc
     */
    public function getCopiesAndDeckLimit()
    {
        $copiesAndDeckLimit = [];
        foreach ($this->slots as $slot) {
            $cardName = $slot->getCard()->getName();
            if (!key_exists($cardName, $copiesAndDeckLimit)) {
                $copiesAndDeckLimit[$cardName] = [
                    'copies' => $slot->getQuantity(),
                    'deck_limit' => $slot->getCard()->getDeckLimit(),
                ];
            } else {
                $copiesAndDeckLimit[$cardName]['copies'] += $slot->getQuantity();
                $copiesAndDeckLimit[$cardName]['deck_limit'] = min(
                    $slot->getCard()->getDeckLimit(),
                    $copiesAndDeckLimit[$cardName]['deck_limit']
                );
            }
        }
        return $copiesAndDeckLimit;
    }

    /**
     * @inheritdoc
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $arr = array();
        foreach ($this->slots as $slot) {
            $arr [$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        ksort($arr);
        return $arr;
    }

    /**
     * Sorting callback.
     * @param SlotInterface $s1
     * @param SlotInterface $s2
     * @return int
     */
    protected function sortByCardCode(SlotInterface $s1, SlotInterface $s2) : int
    {
        return intval($s1->getCard()->getCode(), 10) - intval($s2->getCard()->getCode(), 10);
    }

    /**
     * Sorting callback.
     * @param SlotInterface $s1
     * @param SlotInterface $s2
     * @return int
     */
    protected function sortByCardName(SlotInterface $s1, SlotInterface $s2) : int
    {
        return strcmp($s1->getCard()->getName(), $s2->getCard()->getName()) ?: $this->sortByCardCode($s1, $s2);
    }
}
