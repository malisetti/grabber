<?php

namespace Abbiya\GrabberBundle\Entity\Repository;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseRepository
 *
 * @author seshachalam
 */
use Doctrine\ORM\EntityRepository;

abstract class BaseRepository extends EntityRepository {

    public function persist($entity) {
        $this->_em->persist($entity);
    }

    public function flush() {
        $this->_em->flush();
    }

}
