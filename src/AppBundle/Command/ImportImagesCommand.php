<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ImportImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:import:images')
            ->setDescription('Copy card images https://github.com/fiskhandlarn/doomtrooperdb-json-data')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to the repository'
            )
            ->addArgument(
                'destination_path',
                InputArgument::REQUIRED,
                'Destination path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Copying images...");

        $filesystem = new Filesystem();

        $path = $input->getArgument('path');
        $destinationPath = getcwd() . '/' . $input->getArgument('destination_path');

        try {
            if (!$filesystem->exists($destinationPath)) {
                $filesystem->mkdir($destinationPath, 0775);
            }
        } catch (IOExceptionInterface $exception) {
            echo 'Error creating directory at'. $exception->getPath();
        }

         /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var Card[] $cards */
        $cards = $em->getRepository('AppBundle:Card')->findAll();

        foreach ($cards as $card) {
            $imageURL = $card->getImageUrl();

            if (empty($imageURL)) {
                $output->writeln(sprintf('Skip %s because its image URL is not defined', $card->getName()));
                continue;
            }

            $output->writeln("Copying image for card <info>" . $card->getName() . "</info>: <info>" . $imageURL . "</info>");

            try {
                $filesystem->copy($path . '/'. $imageURL, $destinationPath . '/' . $imageURL);
            } catch (IOExceptionInterface $exception) {
                echo 'Error copying file at'. $exception->getPath();
            }
        }

        $em->flush();

        $output->writeln("Done.");
    }
}
