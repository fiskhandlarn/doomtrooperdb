<?php

namespace AppBundle\Entity;

use AppBundle\Model\ExportableDeck;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * Class Deck
 * @package AppBundle\Entity
 */
class Deck extends ExportableDeck implements JsonSerializable
{
    /**
     * @var string
     */
    private $problem;

    /**
     * @var string
     */
    private $tags;

    /**
     * @var integer
     */
    private $majorVersion;

    /**
     * @var integer
     */
    private $minorVersion;

    /**
     * @var Collection
     */
    private $children;

    /**
     * @var Collection
     */
    private $changes;

    /**
     * @var Expansion
     */
    private $lastExpansion;

    /**
     * @var Decklist
     */
    private $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->changes = new ArrayCollection();
        $this->minorVersion = 0;
        $this->majorVersion = 0;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Deck
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Deck
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Deck
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Set descriptionMd
     *
     * @param string $descriptionMd
     *
     * @return Deck
     */
    public function setDescriptionMd($descriptionMd)
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    /**
     * Set problem
     *
     * @param string $problem
     *
     * @return Deck
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;

        return $this;
    }

    /**
     * Get problem
     *
     * @return string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return Deck
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add slot
     *
     * @param Deckslot $slot
     *
     * @return Deck
     */
    public function addSlot(Deckslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param Deckslot $slot
     */
    public function removeSlot(Deckslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Add child
     *
     * @param Decklist $child
     *
     * @return Deck
     */
    public function addChild(Decklist $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Decklist $child
     */
    public function removeChild(Decklist $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add change
     *
     * @param Deckchange $change
     *
     * @return Deck
     */
    public function addChange(Deckchange $change)
    {
        $this->changes[] = $change;

        return $this;
    }

    /**
     * Remove change
     *
     * @param Deckchange $change
     */
    public function removeChange(Deckchange $change)
    {
        $this->changes->removeElement($change);
    }

    /**
     * Get changes
     *
     * @return Collection
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Deck
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set lastExpansion
     *
     * @param Expansion $lastExpansion
     *
     * @return Deck
     */
    public function setLastExpansion(Expansion $lastExpansion = null)
    {
        $this->lastExpansion = $lastExpansion;

        return $this;
    }

    /**
     * Get lastExpansion
     *
     * @return Expansion
     */
    public function getLastExpansion()
    {
        return $this->lastExpansion;
    }

    /**
     * Set parent
     *
     * @param Decklist $parent
     *
     * @return Deck
     */
    public function setParent(Decklist $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Decklist
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set majorVersion
     *
     * @param integer $majorVersion
     *
     * @return Deck
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;

        return $this;
    }

    /**
     * Get majorVersion
     *
     * @return integer
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }

    /**
     * Set minorVersion
     *
     * @param integer $minorVersion
     *
     * @return Deck
     */
    public function setMinorVersion($minorVersion)
    {
        $this->minorVersion = $minorVersion;

        return $this;
    }

    /**
     * Get minorVersion
     *
     * @return integer
     */
    public function getMinorVersion()
    {
        return $this->minorVersion;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->majorVersion . "." . $this->minorVersion;
    }

    /**
     * @return array
     */
    public function getHistory()
    {
        $slots = $this->getSlots();
        $cards = $slots->getContent();

        $snapshots = [];

        /**
         * All changes, with the newest at position 0
         */
        $changes = $this->getChanges();

        /**
         * Saved changes, with the newest at position 0
         * @var $savedChanges Deckchange[]
         */
        $savedChanges = [];

        /**
         * Unsaved changes, with the oldest at position 0
         * @var $unsavedChanges Deckchange[]
         */
        $unsavedChanges = [];

        foreach ($changes as $change) {
            if ($change->getIsSaved()) {
                array_push($savedChanges, $change);
            } else {
                array_unshift($unsavedChanges, $change);
            }
        }

        // recreating the versions with the variation info, starting from $preversion
        $preversion = $cards;

        foreach ($savedChanges as $change) {
            $variation = json_decode($change->getVariation(), true);

            $row = [
                'variation' => $variation,
                'is_saved' => $change->getIsSaved(),
                'version' => $change->getVersion(),
                'content' => $preversion,
                'date_creation' => $change->getDateCreation()->format('c'),
            ];
            array_unshift($snapshots, $row);

            // applying variation to create 'next' (older) preversion
            foreach ($variation[0] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    continue;
                }
                $preversion[$code] = $preversion[$code] - $qty;
                if ($preversion[$code] == 0) {
                    unset($preversion[$code]);
                }
            }
            foreach ($variation[1] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    $preversion[$code] = 0;
                }
                $preversion[$code] = $preversion[$code] + $qty;
            }
            ksort($preversion);
        }

        // add last know version with empty diff
        $row = [
            'variation' => null,
            'is_saved' => true,
            'version' => "0.0",
            'content' => $preversion,
            'date_creation' => $this->getDateCreation()->format('c')
        ];
        array_unshift($snapshots, $row);

        // recreating the snapshots with the variation info, starting from $postversion
        $postversion = $cards;
        foreach ($unsavedChanges as $change) {
            $variation = json_decode($change->getVariation(), true);
            $row = [
                'variation' => $variation,
                'is_saved' => $change->getIsSaved(),
                'version' => $change->getVersion(),
                'date_creation' => $change->getDateCreation()->format('c'),
            ];

            // applying variation to postversion
            foreach ($variation[0] as $code => $qty) {
                if (!isset($postversion[$code])) {
                    $postversion[$code] = 0;
                }
                $postversion[$code] = $postversion[$code] + $qty;
            }
            foreach ($variation[1] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    continue;
                }
                $postversion[$code] = $postversion[$code] - $qty;
                if ($postversion[$code] == 0) {
                    unset($postversion[$code]);
                }
            }
            ksort($postversion);

            // add postversion with variation that lead to it
            $row['content'] = $postversion;
            array_push($snapshots, $row);
        }

        return $snapshots;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $array = parent::getArrayExport();
        $array['problem'] = $this->getProblem();
        $array['tags'] = $this->getTags();

        return $array;
    }

    /**
     * @return boolean
     */
    public function getIsUnsaved()
    {
        $changes = $this->getChanges();

        foreach ($changes as $change) {
            if (!$change->getIsSaved()) {
                return true;
            }
        }

        return false;
    }
}
