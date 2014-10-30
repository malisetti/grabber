<?php

namespace Abbiya\GrabberBundle\Command;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NormalizeUrlsCommand
 *
 * @author seshachalam
 */
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NormalizeUrlsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('url:norm')
                ->setDescription('normalizes urls')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $cpRepo = $em->getRepository('AbbiyaGrabberBundle:CP');
        
        $urls = $cpRepo->findAll();
        foreach ($urls as $url){
            $pUrl = new \Purl\Url($url->getUrl());
            $output->writeln($url->getUrl());
            $query = $pUrl->query->getQuery();
            $newUrl = str_replace($query, '', $pUrl);
            $output->writeln($newUrl);
            $url->setUrl($newUrl);
        }
        $em->flush();
    }

}
