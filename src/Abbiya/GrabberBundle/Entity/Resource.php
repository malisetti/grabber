<?php

namespace Abbiya\GrabberBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Resource
 *
 * @author seshachalam
 */
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class Resource extends BaseEntity {

    /**
     * @ORM\Column(type="text")
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    protected $hash;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $visited;


    /**
     * Set url
     *
     * @param string $url
     * @return Resource
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Resource
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set visited
     *
     * @param boolean $visited
     * @return Resource
     */
    public function setVisited($visited)
    {
        $this->visited = $visited;

        return $this;
    }

    /**
     * Get visited
     *
     * @return boolean 
     */
    public function getVisited()
    {
        return $this->visited;
    }
    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
        // Add your code here
        parent::setUpdatedValue();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValues()
    {
        parent::setCreatedValues();
        $this->hash = sha1($this->url);
    }
}
