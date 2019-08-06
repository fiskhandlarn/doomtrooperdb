<?php

namespace AppBundle\Entity;

class Card implements \Serializable
{
    private function snakeToCamel($snake)
    {
        $parts = explode('_', $snake);
        return implode('', array_map('ucfirst', $parts));
    }

    public function serialize()
    {
        $serialized = [];
        if (empty($this->code)) {
            return $serialized;
        }

        $mandatoryFields = [
            'code',
            'deck_limit',
            'name',
            'rarity',
            'octgn_id'
        ];

        $optionalFields = [
            'clarification_text',
            'flavor',
            'illustrator',
            'image_url',
            'notes',
            'post_play',
            'text',
        ];

        $externalFields = [
            'faction',
            'expansion',
            'type'
        ];

        switch ($this->type->getCode()) {
            case 'warrior':
            case 'warzone':
                $mandatoryFields[] = 'armor';
                $mandatoryFields[] = 'fight';
                $mandatoryFields[] = 'shoot';
                $mandatoryFields[] = 'value';
                break;
        }

        foreach ($optionalFields as $optionalField) {
            $getter = 'get' . $this->snakeToCamel($optionalField);
            $serialized[$optionalField] = $this->$getter();
            if (!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') {
                unset($serialized[$optionalField]);
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            $getter = 'get' . $this->snakeToCamel($mandatoryField);
            $serialized[$mandatoryField] = $this->$getter();
        }

        foreach ($externalFields as $externalField) {
            $getter = 'get' . $this->snakeToCamel($externalField);
            $serialized[$externalField.'_code'] = $this->$getter()->getCode();
        }

        ksort($serialized);
        return $serialized;
    }

    public function unserialize($serialized)
    {
        throw new \Exception("unserialize() method unsupported");
    }

    public function toString()
    {
        return $this->name;
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var integer
     */
    private $deckLimit;


    /**
     * @var string|null
     */
    private $armor;

    /**
     * @var string|null
     */
    private $clarificationText;

    /**
     * @var string|null
     */
    private $fight;

    /**
     * @var string|null
     */
    private $flavor;

    /**
     * @var string|null
     */
    private $illustrator;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var string|null
     */
    private $notes;

    /**
     * @var string
     */
    private $octgnId;

    /**
     * @var string|null
     */
    private $postPlay;

    /**
     * @var string
     */
    private $rarity;

    /**
     * @var string|null
     */
    private $shoot;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;

    /**
     * @var \AppBundle\Entity\Expansion
     */
    private $expansion;

    /**
     * @var \AppBundle\Entity\Type
     */
    private $type;

    /**
     * @var \AppBundle\Entity\Faction
     */
    private $faction;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Card
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Card
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Card
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set deckLimit
     *
     * @param integer $deckLimit
     *
     * @return Card
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;

        return $this;
    }

    /**
     * Get deckLimit
     *
     * @return integer
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * Set flavor
     *
     * @param string $flavor
     *
     * @return Card
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * Get flavor
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Set illustrator
     *
     * @param string $illustrator
     *
     * @return Card
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * Get illustrator
     *
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * Set octgnId
     *
     * @param boolean $octgnId
     *
     * @return Card
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;

        return $this;
    }

    /**
     * Get octgnId
     *
     * @return boolean
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    /**
     * Add review
     *
     * @param \AppBundle\Entity\Review $review
     *
     * @return Card
     */
    public function addReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \AppBundle\Entity\Review $review
     */
    public function removeReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set expansion
     *
     * @param \AppBundle\Entity\Expansion $expansion
     *
     * @return Card
     */
    public function setExpansion(\AppBundle\Entity\Expansion $expansion = null)
    {
        $this->expansion = $expansion;

        return $this;
    }

    /**
     * Get expansion
     *
     * @return \AppBundle\Entity\Expansion
     */
    public function getExpansion()
    {
        return $this->expansion;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Type $type
     *
     * @return Card
     */
    public function setType(\AppBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set faction
     *
     * @param \AppBundle\Entity\Faction $faction
     *
     * @return Card
     */
    public function setFaction(\AppBundle\Entity\Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return \AppBundle\Entity\Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return self
     */
    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * @param string $armor
     *
     * @return Card
     */
    public function setArmor($armor)
    {
        $this->armor = $armor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClarificationText()
    {
        return $this->clarificationText;
    }

    /**
     * @param string $clarificationText
     *
     * @return Card
     */
    public function setClarificationText($clarificationText)
    {
        $this->clarificationText = $clarificationText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFight()
    {
        return $this->fight;
    }

    /**
     * @param string $fight
     *
     * @return Card
     */
    public function setFight($fight)
    {
        $this->fight = $fight;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return Card
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostPlay()
    {
        return $this->postPlay;
    }

    /**
     * @param string $postPlay
     *
     * @return Card
     */
    public function setPostPlay($postPlay)
    {
        $this->postPlay = $postPlay;

        return $this;
    }

    /**
     * @return string
     */
    public function getRarity()
    {
        return $this->rarity;
    }

    /**
     * @param string $rarity
     *
     * @return Card
     */
    public function setRarity($rarity)
    {
        $this->rarity = $rarity;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShoot()
    {
        return $this->shoot;
    }

    /**
     * @param string $shoot
     *
     * @return Card
     */
    public function setShoot($shoot)
    {
        $this->shoot = $shoot;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Card
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

}
