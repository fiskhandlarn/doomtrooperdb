<?php

namespace AppBundle\Model;

use AppBundle\Entity\Faction;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * Base class for both deck and deck-list models.
 *
 * @package AppBundle\Model
 *
 * @todo rename this into something that better reflects its purpose. [ST 2019/01/20]
 * @todo pull setters up [ST 2019/01/20]
 */
abstract class ExportableDeck implements SlotCollectionProviderInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Collection
     */
    protected $slots;

    /**
     * @var \DateTime
     */
    protected $dateCreation;

    /**
     * @var \DateTime
     */
    protected $dateUpdate;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Faction
     */
    protected $faction;

    /**
     * @var string
     */
    protected $descriptionMd;

    /**
     * @return mixed
     */
    abstract public function getVersion();

    /**
     * Get faction
     *
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @return string
     */
    public function getDescriptionMd()
    {
        return $this->descriptionMd;
    }

    /**
     * @return SlotCollectionInterface
     */
    public function getSlots()
    {
        return new SlotCollectionDecorator($this->slots);
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Transforms the given object into an associative array.
     * @return array
     */
    public function getArrayExport()
    {
        $slots = $this->getSlots();
        $array = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'date_creation' => $this->getDateCreation()->format('c'),
            'date_update' => $this->getDateUpdate()->format('c'),
            'description_md' => $this->getDescriptionMd(),
            'user_id' => $this->getUser()->getId(),
            'faction_code' => $this->getFaction()->getCode(),
            'faction_name' => $this->getFaction()->getName(),
            'slots' => $slots->getContent(),
            'version' => $this->getVersion(),
            'isLegalForJoust' => $this->isLegalForJoust(),
            'isLegalForMelee' => $this->isLegalForMelee(),

        ];

        return $array;
    }

    /**
     * @return array
     */
    public function getTextExport()
    {
        $slots = $this->getSlots();
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'faction' => $this->getFaction(),
            'draw_deck_size' => $slots->getDrawDeck()->countCards(),
            'plot_deck_size' => $slots->getPlotDeck()->countCards(),
            'included_expansions' => $slots->getIncludedExpansions(),
            'slots_by_type' => $slots->getSlotsByType()
        ];
    }

    /**
     * @return array
     */
    public function getExpansionOrderExport()
    {
        $slots = $this->getSlots();
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'faction' => $this->getFaction(),
            'draw_deck_size' => $slots->getDrawDeck()->countCards(),
            'plot_deck_size' => $slots->getPlotDeck()->countCards(),
            'included_expansions' => $slots->getIncludedExpansions(),
            'slots_by_expansion_order' => $slots->getSlotsByExpansionOrder()
        ];
    }

    /**
     * @return boolean
     * @see SlotCollectionInterface::isLegalForMelee()
     */
    public function isLegalForMelee()
    {
        return $this->getSlots()->isLegalForMelee();
    }

    /**
     * @return boolean
     * @see SlotCollectionInterface::isLegalForJoust()
     */
    public function isLegalForJoust()
    {
        return $this->getSlots()->isLegalForJoust();
    }
}
