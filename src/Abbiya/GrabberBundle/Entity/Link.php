<?php

namespace Abbiya\GrabberBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Link
 *
 * @author seshachalam
 */
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Abbiya\GrabberBundle\Entity\Repository\LinkRepository")
 */
class Link extends Resource {

    /**
     * @ORM\ManyToMany(targetEntity="Link", mappedBy="referringTo", cascade={"persist"})
     * */
    private $referredBy;

    /**
     * @ORM\ManyToMany(targetEntity="Link", inversedBy="referredBy", cascade={"persist"})
     * @ORM\JoinTable(name="pointers",
     *      joinColumns={@ORM\JoinColumn(name="link_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="referred_link_id", referencedColumnName="id")}
     *      )
     * */
    private $referringTo;

    /**
     * @ORM\OneToMany(targetEntity="Asset", mappedBy="parent", cascade={"persist"})
     */
    private $assets;

    /**
     * Constructor
     */
    public function __construct() {
        $this->referredBy = new ArrayCollection();
        $this->referringTo = new ArrayCollection();
        $this->assets = new ArrayCollection();
    }

    public function setUrl($url) {
        $this->url = $url;
        $this->hash = sha1($url);
        
        return $this;
    }

    /**
     * Add referredBy
     *
     * @param \Abbiya\GrabberBundle\Entity\Link $referredBy
     * @return Link
     */
    public function addReferredBy(\Abbiya\GrabberBundle\Entity\Link $referredBy = null) {
        if ($referredBy) {
            $this->referredBy[] = $referredBy;
        }

        return $this;
    }

    /**
     * Remove referredBy
     *
     * @param \Abbiya\GrabberBundle\Entity\Link $referredBy
     */
    public function removeReferredBy(\Abbiya\GrabberBundle\Entity\Link $referredBy) {
        $this->referredBy->removeElement($referredBy);
    }

    /**
     * Get referredBy
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferredBy() {
        return $this->referredBy;
    }

    /**
     * Add referringTo
     *
     * @param \Abbiya\GrabberBundle\Entity\Link $referringTo
     * @return Link
     */
    public function addReferringTo(\Abbiya\GrabberBundle\Entity\Link $referringTo = null) {
        if ($referringTo) {
            $this->referringTo[] = $referringTo;
        }

        return $this;
    }

    /**
     * Remove referringTo
     *
     * @param \Abbiya\GrabberBundle\Entity\Link $referringTo
     */
    public function removeReferringTo(\Abbiya\GrabberBundle\Entity\Link $referringTo) {
        $this->referringTo->removeElement($referringTo);
    }

    /**
     * Get referringTo
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferringTo() {
        return $this->referringTo;
    }

    /**
     * Add assets
     *
     * @param \Abbiya\GrabberBundle\Entity\Asset $assets
     * @return Link
     */
    public function addAsset(\Abbiya\GrabberBundle\Entity\Asset $assets) {
        $this->assets[] = $assets;

        return $this;
    }

    /**
     * Remove assets
     *
     * @param \Abbiya\GrabberBundle\Entity\Asset $assets
     */
    public function removeAsset(\Abbiya\GrabberBundle\Entity\Asset $assets) {
        $this->assets->removeElement($assets);
    }

    /**
     * Get assets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssets() {
        return $this->assets;
    }

}
