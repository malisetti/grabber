<?php

namespace Abbiya\GrabberBundle\Services;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParseHtml
 *
 * @author seshachalam
 */
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Purl\Url;
use Mmoreram\GearmanBundle\Service\GearmanClient;
use Mmoreram\GearmanBundle\Driver\Gearman;
use BCC\ResqueBundle\Resque;
use Abbiya\GrabberBundle\Utils;
use Abbiya\GrabberBundle\Entity\Link;
use Abbiya\GrabberBundle\Entity\Asset;
use Abbiya\GrabberBundle\Entity\Repository\BaseRepository;
use Abbiya\GrabberBundle\Worker\SortJob;
use Abbiya\GrabberBundle\Worker\AssetSortJob;

/**
 * @Gearman\Work(
 *     name = "ParseHtml",
 *     service="parse.html",
 *     description = "scrapes html")
 */
class ParseHtml {

    private $baseUrl;
    private $html;
    private $client;
    private $crawler;
    private $linkRepository;
    private $resque;

    public function __construct() {
        $this->html = null;
        try {
            $this->client = new Client();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function setLinkRepository(BaseRepository $repo) {
        $this->linkRepository = $repo;
    }

    public function setResque(Resque $resque) {
        $this->resque = $resque;
    }

    /**
     * method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     name = "getHtml",
     *     description = "gets html")
     */
    public function getPageHtml(\GearmanJob $job) {
        $seedUrl = $job->workload();
        if (strpos($seedUrl, 'http') === false) { // http protocol not included, prepend it to the base url
            $seedUrl = 'http://' . $seedUrl;
        }

        $purl = new Url($seedUrl);
        $this->baseUrl = $purl->scheme . '://' . $purl->host;
        if ($this->checkIfCrawlable($seedUrl)) {
            //check if the url is already crawled
            if ($this->linkRepository->findOneBy(array('url' => $seedUrl, 'visited' => true))) {
                return true;
            }
            try {
                $this->crawler = $this->client->request('GET', $this->normalizeLink($seedUrl));
            } catch (\GuzzleHttp\Exception\AdapterException $e) {
                error_log($e->getMessage());
                return true;
            } catch (\Exception $e) {
                error_log($e->getMessage());
                return true;
            }
        }

        $statusCode = 0;
        try {
            $statusCode = $this->client->getResponse()->getStatus();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return true;
        }
        if ((int)$statusCode !== 200) {
            error_log('Could not fetch this url content. error with ' . $statusCode);
            return true;
        } else {
            //store the seed url in db
            $seedLink = new Link();
            $seedLink->setUrl($seedUrl);
            $existingSeedLink = Utils::insertIfNotExist($this->linkRepository, $seedLink, 'hash');
            if ($existingSeedLink !== false) {
                if($existingSeedLink->getVisited() === true){
                    return true;
                }
                $existingSeedLink->setVisited(true);
            }
            try{
                $this->linkRepository->flush();
            }catch(\PDOException $e){
                error_log($e->getMessage());
            }  catch (\Exception $e){
                error_log($e->getMessage());
            }

            $urls = $this->parse('a', 'href');
            $urls = $this->prepareLinks($urls);
            
            foreach ($urls as $url) {
                if ($this->checkIfCrawlable($url)) {
                    $sortJob = new SortJob();
                    $sortJob->queue = 'glue-links-queue';
                    $sortJob->args = array('referred_by' => $seedLink->getUrl(), 'url' => $url);
                    
                    try{
                        $this->resque->enqueue($sortJob, true);
                    }  catch (\Exception $e){
                        error_log($e->getMessage());
                        
                        return false;
                    }
                }
            }

            $images = $this->parse('img', 'src');
            $images = $this->prepareLinks($images);

            foreach ($images as $image) {
                if ($this->checkIfCrawlable($image)) {

                    $sortJob = new AssetSortJob();
                    $sortJob->queue = 'glue-assets-queue';
                    $sortJob->args = array('referred_by' => $seedLink->getUrl(), 'url' => $image);
                    try{
                        $this->resque->enqueue($sortJob, true);
                    }catch(\Exception $e){
                        error_log($e->getMessage());
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * checks the uri if can be crawled or not
     * in order to prevent links like "javascript:void(0)" or "#something" from being crawled again
     * @param string $uri
     * @return boolean
     */
    protected function checkIfCrawlable($uri) {
        if (empty($uri)) {
            return false;
        }

        $stopLinks = array(//returned deadlinks
            '@^javascript\:void\(0\)$@',
            '@^#.*@',
            '@^javascript\:void\(0\);$@',
        );

        foreach ($stopLinks as $ptrn) {
            if (preg_match($ptrn, $uri)) {
                return false;
            }
        }

        return true;
    }

    /**
     * normalize link before visiting it
     * currently just remove url hash from the string
     * @param string $uri
     * @return string
     */
    protected function normalizeLink($uri) {
        $uri = preg_replace('@#.*$@', '', $uri);

        return $uri;
    }

    private function parse($filter, $attr) {
        $currentLinks = array();
        $this->crawler->filter($filter)->each(function(Crawler $node, $i) use (&$currentLinks, $attr) {
            $nodeUrl = $node->attr($attr);
            $hash = $this->normalizeLink($nodeUrl);
            if (!$this->checkIfCrawlable($nodeUrl)) {
                
            } elseif (!preg_match("@^http(s)?@", $nodeUrl)) { //not absolute link                            
                $currentLinks[] = $this->baseUrl . $hash;
            } else {
                $currentLinks[] = $hash;
            }
        });

        return $currentLinks;
    }

    private function prepareLinks($urls) {
        $urls = array_unique($urls);
        foreach ($urls as $index => $url) {
            if (strpos($url, $this->baseUrl, 0) !== false && strpos($url, $this->baseUrl . '/', 0) === false) {
                $url = str_replace($this->baseUrl, $this->baseUrl . '/', $url);
                unset($urls[$index]);

                if (!$this->checkIfCrawlable($url)) {
                    if (strpos($url, '/', 0) === false) {
                        $url = '/' . $url;
                    }
                    $pUrl = \Purl\Url::parse($this->baseUrl)->set('path', $url);
                    $url = $pUrl->getUrl();
                    $url = $this->normalizeLink($url);
                }

                $urls[] = $url;
            }
        }

        return $urls;
    }

}
