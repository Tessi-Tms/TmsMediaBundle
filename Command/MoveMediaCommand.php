<?php

namespace Tms\Bundle\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;

class MoveMediaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tms-media:move')
            ->setDescription('Move media files following to their metadata')
            ->addOption('provider', 'p', InputOption::VALUE_REQUIRED, 'The media provider service name', 'default_media')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The limit to processed', 10000)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'The offset to processed', 1)
            ->addOption('force','f', InputOption::VALUE_NONE, 'if present, the files will be moved')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

<info>php app/console %command.name% -f </info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $manager = $this->getContainer()->get('tms_media.manager.media');
        $newProviderServiceName = $input->getOption('provider');
        $newMediaProvider = $manager
            ->getFilesystemMap()
            ->get($newProviderServiceName)
        ;

        $medias = $manager
            ->findBy(
                array(),
                array(),
                $input->getOption('limit'),
                $input->getOption('offset')
            )
        ;
        $moved = array();

        $progress = new ProgressBar($output, count($medias));
        $output->writeln('');
        $progress->start();
        $table = new Table($output);
        $table->setHeaders(array('Action', 'ID', 'Reference', 'FROM', 'TO'));

        foreach ($medias as $media) {
            $newPrefix = $manager::guessReferencePrefix($media);
            $oldPrefix = $media->getReferencePrefix();
            $oldProviderServiceName = $media->getProviderServiceName();

            if ($oldPrefix === $newPrefix) {
                $table->addRow(array(
                    'UNCHANGED',
                    $media->getId(),
                    $media->getReference(),
                    sprintf('%s (/%s)', $oldProviderServiceName, $oldPrefix),
                    sprintf('%s (/%s)', $newProviderServiceName, $newPrefix)
                ));

                continue;
            }

            try {
                $moved[] = $media;
                $action = 'TO MOVE';
                $oldMediaProvider = $manager
                    ->getFilesystemMap()
                    ->get($oldProviderServiceName)
                ;

                if ($input->getOption('force')) {
                    $action = 'MOVED';
                    $newMediaProvider->write(
                        $manager->buildStorageKey($newPrefix, $media->getReference()),
                        $oldMediaProvider->read($manager->buildStorageKey($oldPrefix, $media->getReference()))
                    );

                    $oldMediaProvider->delete($manager->buildStorageKey($oldPrefix, $media->getReference()));

                    $media->setReferencePrefix($newPrefix);
                    $media->setProviderServiceName($newProviderServiceName);
                    $manager->update($media);
                }

                $table->addRow(array(
                    $action,
                    $media->getId(),
                    $media->getReference(),
                    sprintf('%s (/%s)', $oldProviderServiceName, $oldPrefix),
                    sprintf('%s (/%s)', $newProviderServiceName, $newPrefix)
                ));
            } catch (\Exception $e) {
                $table->addRow(array(
                    sprintf('Error: %s', $e->getMessage()),
                    $media->getId(),
                    $media->getReference(),
                    sprintf('%s (/%s)', $oldProviderServiceName, $oldPrefix),
                    sprintf('%s (/%s)', $newProviderServiceName, $newPrefix)
                ));
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('');

        $table->setStyle('borderless');
        $table->render();

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;

        $output->writeln('');
        $output->writeln(sprintf(
            '<comment>%d media processed [%d sec]</comment>',
            count($moved),
            $time
        ));
    }
}
