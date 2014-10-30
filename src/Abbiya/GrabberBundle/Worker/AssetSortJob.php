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
use Abbiya\GrabberBundle\Entity\Asset;
use Abbiya\GrabberBundle\Entity\Link;

class AssetSortJob extends ContainerAwareJob {

    public function run($args) {
        $referredBy = $args['referred_by'];
        $url = $args['url'];

        $linkRepo = $this->getContainer()->get('link_repository');
        $seedLink = new Link();
        $seedLink->setUrl($referredBy);

        $seedLink = Utils::insertIfNotExist($linkRepo, $seedLink, 'hash');

        $this->dealWithAsset($url, $seedLink);
    }

    private function dealWithAsset($url, Link $seedLink) {
        $assetRepo = $this->getContainer()->get('asset_repository');

        $asset = new Asset();
        $asset->setUrl($url)->setParent($seedLink);
        $oldAsset = Utils::insertIfNotExist($assetRepo, $asset, 'hash');
        if ($oldAsset !== false) {
            $seedLink->addAsset($oldAsset);
        } else {
            $seedLink->addAsset($asset);
        }
        try {
            $assetRepo->flush();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

}
