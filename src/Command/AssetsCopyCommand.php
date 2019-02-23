<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AssetsCopyCommand
 * @package App\Command
 */
class AssetsCopyCommand extends Command
{
    protected static $defaultName = 'app:assets-copy';

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @inheritDoc
     */
    public function __construct(ParameterBagInterface $params, Filesystem $file_system, $name = null)
    {
        parent::__construct($name);
        $this->params = $params;
        $this->fileSystem = $file_system;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Copy assets from public/build to dist/build');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $root = $this->params->get('kernel.project_dir') . DIRECTORY_SEPARATOR;

        $source = $root . "public/build";

        $target = $root . "dist/build";

        $this->fileSystem->mirror($source, $target);

        $io->success('Done!');
    }
}
