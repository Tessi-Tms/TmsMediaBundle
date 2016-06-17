<?php

namespace Tms\Bundle\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;


class MoveMediaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tms-media:move')
            ->setDescription('Move media files following to their metadata')
            ->addOption('force','f', InputOption::VALUE_NONE, 'if present the files will be moved')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit the number of media to process', 10000)
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

<info>php app/console %command.name% -f </info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('tms_media.manager.media');

        $medias = $manager
            ->findBy(
                array('referencePrefix' => null),
                array(),
                $input->getOption('limit')
            )
        ;

        $output->writeln(sprintf('<info>Start to move %s media</info>', count($medias)));

        foreach ($medias as $media) {
            $provider = $manager->getFilesystemMap()->get($media->getProviderServiceName());
            $prefix = $manager::guessReferencePrefix($media->getMetadata());

            if (empty($prefix)) {
                $output->writeln(sprintf('<error>[%d] Media (%s) has no prefix</error>',
                    $media->getId(),
                    $media->getReference()
                ));

                continue;
            }

            $output->writeln(sprintf('<info>[%d] Media (%s) with  metadata: %s to move to: %s</info>',
                $media->getId(),
                $media->getReference(),
                json_encode($media->getMetadata()),
                $prefix
            ));

            if ($input->getOption('force')) {
                try {
                    $provider->write(
                        $manager->buildStorageKey($prefix, $media->getReference()),
                        $provider->read($media->getReference())
                    );

                    $provider->delete($media->getReference());

                    $media->setReferencePrefix($prefix);
                    $media->setProviderServiceName('web_images');
                    $manager->update($media);
                } catch (\Exception $e) {
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                }
            }
        }
    }
}