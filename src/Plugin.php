<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => \RezoZero\Xilofone\Composer\CommandProvider::class
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        ];
    }

    public function onPostUpdateCmd(Event $event): void
    {
        $event->getIO()->write('Fetching new translations files from Xilofone');
        $factory = new XilofoneFileProviderFactory($event->getComposer());
        try {
            $xilofoneFileProvider = $factory->create();
            $translatedFiles = $xilofoneFileProvider->getXilofoneTranslatedFiles();
            $xilofoneFileProvider->storeTranslatedFiles($translatedFiles);
        } catch (\Exception $e) {
            $event->getIO()->warning('Impossible to download translations files from Xilofone. ' . $e->getMessage());
        }
    }
}
