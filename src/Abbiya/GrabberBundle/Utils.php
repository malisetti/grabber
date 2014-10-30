<?php

namespace Abbiya\GrabberBundle;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utils
 *
 * @author seshachalam
 */
class Utils {

    public static function insertIfNotExist($repo, $entity, $criteria) {
        $obj = new \ReflectionObject($entity);
        $property = $obj->getProperty($criteria);
        $property->setAccessible(true);
        $cVal = $property->getValue($entity);
        $oldEntity = $repo->findOneBy(array($criteria => $cVal));
        if (empty($oldEntity)) {
            $repo->persist($entity);
            return false;
        }

        return $oldEntity;
    }

}
