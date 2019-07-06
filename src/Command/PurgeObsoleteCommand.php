<?php

namespace App\Command;

use App\Entity\Link;
use App\Service\UrlCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class PurgeObsoleteCommand
 * @package App\Command
 */
class PurgeObsoleteCommand extends Command
{
    protected static $defaultName = 'app:purge-obsolete';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlCheckerService
     */
    private $urlCheckerService;

    /**
     * @inheritDoc
     */
    public function __construct(EntityManagerInterface $entityManager, UrlCheckerService $urlCheckerService, string $name = null)
    {
        $this->entityManager = $entityManager;
        $this->urlCheckerService = $urlCheckerService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Delete links that returns 404 error');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->entityManager->getRepository(Link::class);

        $links = $repo->findAll();

        foreach ($links as $link) {
            $code = $this->urlCheckerService->checkUrl($link->getUrl());
            if ($code >= 400) {
                $io->note($link->getUrl() . " response code: " . $code);
                $this->entityManager->remove($link);
            }
        }

        $this->entityManager->flush();

        $io->success('Done!!!');
    }
}
