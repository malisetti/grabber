<?php

namespace Abbiya\GrabberBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Asset
 *
 * @author seshachalam
 */
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Abbiya\GrabberBundle\Entity\Repository\AssetRepository")
 */
class Asset extends Resource {

    /**
     * @ORM\ManyToOne(targetEntity="Link", inversedBy="assets", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    public function setUrl($url) {
        $this->url = $url;
        $this->hash = sha1($url);
        
        return $this;
    }

    /**
     * Set parent
     *
     * @param \Abbiya\GrabberBundle\Entity\Link $parent
     * @return Asset
     */
    public function setParent(\Abbiya\GrabberBundle\Entity\Link $parent = null) {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Abbiya\GrabberBundle\Entity\Link 
     */
    public function getParent() {
        return $this->parent;
    }

}
