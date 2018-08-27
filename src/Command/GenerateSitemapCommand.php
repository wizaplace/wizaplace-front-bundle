<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SitemapGenerator\Sitemap\Sitemap;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSitemapCommand extends ContainerAwareCommand
{
    private $sitemap;

    public function __construct(Sitemap $sitemap)
    {
        $this->sitemap = $sitemap;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Generate the sitemap')
            ->setName('wizaplace:sitemap:generate');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->section("Generating the sitemap...");
        $io->text($this->sitemap->build());
        $io->success("Sitemap successfully generated!");
    }
}
