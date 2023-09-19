<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer\Command;

use Composer\Command\BaseCommand;
use RezoZero\Xilofone\Composer\XilofoneFileProviderFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FetchXilofoneFiles extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('xilofone:fetch-files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching new translations files from Xilofone');
        $composer = $this->getComposer();
        if (null === $composer) {
            throw new \RuntimeException('Composer is null');
        }
        $factory = new XilofoneFileProviderFactory($composer);
        $xilofoneFileProvider = $factory->create();
        $translatedFiles = $xilofoneFileProvider->getXilofoneTranslatedFiles();
        foreach ($translatedFiles as $file) {
            $output->writeln('Downloaded '.$file->getName(). ' into ' . $file->getPath());
        }
        $xilofoneFileProvider->storeTranslatedFiles($translatedFiles);
        return 0;
    }
}
