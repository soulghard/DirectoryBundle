<?php

namespace CCETC\DirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @Orm\MappedSuperclass
 */
class BaseAttribute extends BaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean $searchable
     *
     * @ORM\Column(name="searchable", type="boolean", nullable=true)
     */
    private $searchable = true;    
    
    
    public function __toString()
    {
        return $this->getName()."";
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
     * Set name
     *
     * @param string $name
     * @return Attribute
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
     * Set searchable
     *
     * @param string $searchable
     * @return Attribute
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    
        return $this;
    }

    /**
     * Get searchable
     *
     * @return string 
     */
    public function getSearchable()
    {
        return $this->searchable;
    }
}