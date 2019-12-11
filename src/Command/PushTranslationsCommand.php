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
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;
use Wizaplace\SDK\Translation\TranslationService;
use WizaplaceFrontBundle\Service\AuthenticationService;

class PushTranslationsCommand extends Command
{
    private const CATALOG_DOMAIN = 'messages';
    private const LOGGER_HEADER = 'Translations:Push';

    /** @var TranslatorBagInterface */
    private $translatorBag;

    /** @var XliffFileDumper */
    private $translationDumper;

    /** @var TranslationService */
    private $translationService;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var LoggerInterface */
    private $logger;

    /** @var string[] */
    private $locales;

    /** @var string */
    private $defaultLocale;

    /** @var string */
    private $systemUserPassword;

    public function __construct(
        TranslatorBagInterface $translatorBag,
        XliffFileDumper $translationDumper,
        TranslationService $translationService,
        AuthenticationService $authenticationService,
        array $locales,
        string $defaultLocale,
        string $systemUserPassword,
        LoggerInterface $logger
    ) {
        $this->translatorBag = $translatorBag;
        $this->translationDumper = $translationDumper;
        $this->translationService = $translationService;
        $this->authenticationService = $authenticationService;
        $this->locales = array_unique($locales);
        $this->defaultLocale = $defaultLocale;
        $this->systemUserPassword = $systemUserPassword;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('wizaplace:translations:push')
            ->setDescription('Push local (in files) translations to the Wizaplace back-end.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->logger->debug(
            self::LOGGER_HEADER,
            ['available locales' => $this->locales]
        );

        array_walk($this->locales, function (string $locale) use ($io): void {
            $io->section("Processing locale '$locale'...");
            $this->executeLocale($locale);
            $io->success("'$locale' locale successfully pushed");
        });

        $fixedKeys = \json_decode(
            $this
                ->translationService
                ->fixMissingKeys($this->systemUserPassword)
                ->getContents()
        );

        $io->success(
            sprintf(
                '%d missing translation keys fixed',
                count($fixedKeys)
            )
        );
        $this->logger->debug(
            self::LOGGER_HEADER,
            ['fixed keys' => $fixedKeys]
        );
    }

    private function executeLocale(string $locale)
    {
        // Load translations, without those we pulled from Wizaplace.
        /** @var MessageCatalogue $catalog */
        $catalog = $this->translatorBag->getCatalogue($locale);

        // Format it as xliff
        $xliffCatalog = $this->translationDumper->formatCatalogue(
            $catalog,
            self::CATALOG_DOMAIN,
            ['default_locale' => $this->defaultLocale]
        );

        $this->logger->debug(
            self::LOGGER_HEADER,
            ['xliff catalog' => $xliffCatalog]
        );

        // Push it to the Wizaplace backend
        $this->translationService->pushXliffCatalog($xliffCatalog, $locale, $this->systemUserPassword);
    }
}
