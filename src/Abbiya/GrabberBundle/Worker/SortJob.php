<?php

namespace Abbiya\GrabberBundle\Worker;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SortJob
 *
 * @author seshachalam
 */
use BCC\ResqueBundle\ContainerAwareJob;
use Abbiya\GrabberBundle\Utils;
use Abbiya\GrabberBundle\Entity\Link;

class SortJob extends ContainerAwareJob {

    public function run($args) {
        $referredBy = $args['referred_by'];
        $url = $args['url'];

        $linkRepo = $this->getContainer()->get('link_repository');
        $seedLink = new Link();
        $seedLink->setUrl($referredBy);

        $seedLink = Utils::insertIfNotExist($linkRepo, $seedLink, 'hash');

        $this->dealWithLink($url, $seedLink);
    }

    private function dealWithLink($url, Link $seedLink) {
        $linkRepo = $this->getContainer()->get('link_repository');

        $link = new Link();
        $link->setUrl($url)->addReferredBy($seedLink)->setVisited(false);

        try {
            $oldLink = Utils::insertIfNotExist($linkRepo, $link, 'hash');
            if ($oldLink !== false) {
                $seedLink->addReferringTo($oldLink);
            } else {
                $seedLink->addReferringTo($link);
            }
            $linkRepo->flush();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        $gearman = $this->getContainer()->get('gearman');
        $gearman->doBackgroundJob('AbbiyaGrabberBundleServicesParseHtml~getHtml', $url);
    }

}
