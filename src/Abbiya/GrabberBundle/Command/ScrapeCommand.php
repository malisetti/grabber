<?php

namespace Abbiya\GrabberBundle\Command;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ScrapeCommand
 *
 * @author seshachalam
 */
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Abbiya\GrabberBundle\Entity\Link;
use Abbiya\GrabberBundle\Utils;

class ScrapeCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('grab:pls')
                ->addArgument('seed', InputArgument::REQUIRED, 'Provide seed url')
                ->setDescription('Grabs images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //take the seed url and start processing
        $seed = $input->getArgument('seed');
        if (!filter_var($seed, FILTER_VALIDATE_URL)) {
            $output->writeln('Please provide a valid url');
            return;
        }

        //store it in links
        $link = new Link();
        $link->setUrl($seed)->setVisited(false)->addReferredBy(null);
        $linkRepo = $this->getContainer()->get('link_repository');
        $oldLink = Utils::insertIfNotExist($linkRepo, $link, 'url');
        if ($oldLink === false || ($oldLink && $oldLink->getVisited() === false)) {
            $this->getContainer()->get('gearman')->doHighBackgroundJob('AbbiyaGrabberBundleServicesParseHtml~getHtml', $seed);
            $linkRepo->flush();

            $output->writeln('link submitted successfully');
        } else {
            $output->writeln('this link is submitted already');
        }
    }

}
