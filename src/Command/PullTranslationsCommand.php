<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wizaplace\SDK\Translation\TranslationService;
use WizaplaceFrontBundle\Service\AuthenticationService;
use Symfony\Component\Finder\Finder;

class PullTranslationsCommand extends Command
{
    /**
     * hash('sha256', '');
     */
    const EMPTY_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

    /** @var TranslationService */
    private $translationService;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var string[] */
    private $locales;

    /** @var string */
    private $translationsDir;

        /** @var string */
    private $cacheDir;

    public function __construct(
        TranslationService $translationService,
        AuthenticationService $authenticationService,
        array $locales,
        $translationsDir,
        $cacheDir
    ) {
        $this->translationService = $translationService;
        $this->authenticationService = $authenticationService;
        $this->locales = $locales;
        $this->translationsDir = $translationsDir;
        $this->cacheDir = $cacheDir;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('wizaplace:translations:pull')
            ->setDescription('Pull translations from Wizaplace backend.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        array_walk($this->locales, function (string $locale) use ($io) : void {
            $io->section("Processing locale '$locale'...");
            $this->executeLocale($locale);
            $io->success("'$locale' locale successfully pulled");
        });
    }

    private function executeLocale(string $locale): void
    {
        $xliffCatalog = $this->translationService->getXliffCatalog($locale);

        $catalogFilePath = "{$this->translationsDir}/messages.{$locale}.xliff";
        $oldHash = self::EMPTY_HASH;

        if (file_exists($catalogFilePath)) {
            $oldHash = hash_file('sha256', $catalogFilePath);
        }

        file_put_contents($catalogFilePath, $xliffCatalog->getContents());
        $newHash = hash_file('sha256', $catalogFilePath);

        // Si le nouveau contenu est identique à l'ancien, pas besoin de flush le cache
        if (hash_equals($oldHash, $newHash)) {
            return;
        }

        if (!file_exists($this->cacheDir)) {
            return;
        }
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->cacheDir)
            ->name("catalogue.{$locale}.*")
        ;

        // On parcours tous les fichiers qui concernent la locale et on les supprime
        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }
    }
}
