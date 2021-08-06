<?php

/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Wizaplace\SDK\Translation\TranslationService;
use WizaplaceFrontBundle\Service\AuthenticationService;

class PullTranslationsCommand extends Command
{
    /**
     * hash('sha256', '');.
     */
    const EMPTY_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
    private const LOGGER_HEADER = 'Translations:Pull';

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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TranslationService $translationService,
        AuthenticationService $authenticationService,
        array $locales,
        string $translationsDir,
        string $cacheDir,
        LoggerInterface $logger
    ) {
        $this->translationService = $translationService;
        $this->authenticationService = $authenticationService;
        $this->locales = $locales;
        $this->translationsDir = $translationsDir;
        $this->cacheDir = $cacheDir;
        $this->logger = $logger;

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
        array_walk(
            $this->locales,
            function (string $locale) use ($io): void {
                $io->section("Processing locale '$locale'...");
                $infoVal = $this->executeLocale($locale);
                if ($infoVal === 'succes') {
                    $io->success("'$locale' locale successfully pulled");
                } elseif ($infoVal === 'error') {
                    $io->error("'$locale' locale unsuccessfully pulled");
                }
            }
        );

        return 0;
    }

    private function executeLocale(string $locale): string
    {
        $xliffCatalog = $this->translationService->getXliffCatalog($locale);

        if (!empty($xliffCatalog)) {
            $catalogFilePath = "{$this->translationsDir}/messages.{$locale}.xliff";
            $oldHash = self::EMPTY_HASH;

            if (file_exists($catalogFilePath)) {
                $oldHash = hash_file('sha256', $catalogFilePath);
            }

            $xliffCatalogContent = $xliffCatalog->getContents();

            $this->logger->debug(
                self::LOGGER_HEADER,
                [
                    'catalogFilePath' => $catalogFilePath,
                    'xliff catalog' => $xliffCatalogContent,
                ]
            );

            file_put_contents($catalogFilePath, $xliffCatalogContent);
            $newHash = hash_file('sha256', $catalogFilePath);

            // Si le nouveau contenu est identique à l'ancien, pas besoin de flush le cache
            if (hash_equals($oldHash, $newHash)) {
                $this->logger->debug(
                    self::LOGGER_HEADER,
                    [
                        'catalogFilePath' => $catalogFilePath,
                        'flush' => "xliff hash: old - $oldHash === new - $newHash , no need to flush",
                    ]
                );

                return 'succes';
            }

            if (!file_exists($this->cacheDir)) {
                $this->logger->debug(
                    self::LOGGER_HEADER,
                    [
                        'catalogFilePath' => $catalogFilePath,
                        'flush' => 'cache dir not exist, no need to flush',
                    ]
                );

                return 'succes';
            }

            $finder = new Finder();
            $finder
                ->files()
                ->in($this->cacheDir)
                ->name("catalogue.{$locale}.*");

            // On parcours tous les fichiers qui concernent la locale et on les supprime
            foreach ($finder as $file) {
                $deleted = unlink($file->getRealPath());

                $this->logger->debug(
                    self::LOGGER_HEADER,
                    [
                        'file' => $file,
                        'deleted' => $deleted,
                    ]
                );
            }
            return 'succes';
        } else {
            return 'error';
        }
    }
}
