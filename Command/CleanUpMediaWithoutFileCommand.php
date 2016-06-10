<?php

namespace Tms\Bundle\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CleanUpMediaWithoutFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tms-media:cleanup:without-file-medias')
            ->setDescription('Display or remove media without associated files')
            // ->addArgument('folderPath', InputArgument::REQUIRED, 'The folder\'s path')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'if present the media record will be removed from entities')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

<info>php app/console %command.name% -f</info>

If you have some doubt about media integrity, you could check it by this way.

<info>php app/console %command.name%</info>

Alternatively, you can clean media entities and remove those have no file associated:

<info>php app/console %command.name% --force</info>

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $output->writeln(sprintf('<comment>Start Media Cleaner</comment>'));

        $mediaManager = $this->getContainer()->get('tms_media.manager.media');

        $medias = $mediaManager->findAll();
        $count = $rcount = $pcount = 0;
        foreach ($medias as $media) {
            try {
                $storageProvider = $mediaManager
                    ->getFilesystemMap()
                    ->get($media->getProviderServiceName())
                ;

                $fileExists = $storageProvider
                    ->getAdapter()
                    ->exists($mediaManager->buildStorageKey(
                        $media->getReferencePrefix(),
                        $media->getReference()
                    ))
                ;

                if ($fileExists) {
                    $output->writeln(sprintf(
                        '<comment>[FOUND] %s // %s</comment>',
                        $media->getProviderServiceName(),
                        $media->getReference()
                    ));
                } else {
                    if ($input->getOption('force')) {
                        $output->writeln(sprintf(
                            '<info>[REMOVE] %s // %s</info>',
                            $media->getProviderServiceName(),
                            $media->getReference()
                        ));

                        $mediaEntityManager->delete($media);
                        $rcount++;
                    } else {
                        $output->writeln(sprintf(
                            '<error>[NOT FOUND] %s // %s</error>',
                            $media->getProviderServiceName(),
                            $media->getReference()
                        ));
                    }

                    $pcount++;
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf(
                    '<error>[ERROR] %s</error>',
                    $e->getMessage()
                ));
            }
            $count++;
        }
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;

        $output->writeln(sprintf(
            '<comment>[DONE] %d sec %d problem encountered on %d entities processed, %d entities removed, %d entities untouched</comment>',
            $time,
            $pcount,
            $count,
            $rcount,
            ($count-$rcount)
        ));
    }
}
