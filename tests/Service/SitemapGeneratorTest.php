<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use SitemapGenerator\Sitemap\Sitemap;
use WizaplaceFrontBundle\Service\SitemapGenerator;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class SitemapGeneratorTest extends BundleTestCase
{
    public function testGenerate()
    {
        $sitemapGenerator = self::$kernel->getContainer()->get('test.WizaplaceFrontBundle\Service\SitemapGenerator');

        /** @var \PHPUnit_Framework_MockObject_MockObject|Sitemap $sitemap */
        $sitemap = $this->createMock(Sitemap::class);

        $spy = self::exactly(29);
        $sitemap->expects($spy)->method('add');

        $sitemapGenerator->populate($sitemap);

        /** @var string[] $urls */
        $urls = array_map(function (ObjectInvocation $invocation): string {
            return $invocation->getParameters()[0]->getLoc();
        }, $spy->getInvocations());

        self::assertContains('http://localhost/', $urls); // static URL
        self::assertContains('http://localhost/a/adidas', $urls); // attribute variant URL
        self::assertContains('http://localhost/p/it/headsets/casque-corsair-gaming', $urls); // product URL
        self::assertContains('http://localhost/c/categorie-principale', $urls); // category URL
        self::assertContains('http://localhost/v/acme', $urls); // company URL
        self::assertContains('http://localhost/faq', $urls); // CMS page URL
    }

    public function testGenerateMultilangual() : void
    {
        $sitemapGenerator = self::$kernel->getContainer()->get('test.Multilanguage.WizaplaceFrontBundle\Service\SitemapGenerator');

        /** @var \PHPUnit_Framework_MockObject_MockObject|Sitemap $sitemap */
        $sitemap = $this->createMock(Sitemap::class);

        $spy = self::exactly(29);
        $sitemap->expects($spy)->method('add');

        $sitemapGenerator->populate($sitemap);

        /** @var string[] $urls */
        $urls = array_map(function (ObjectInvocation $invocation): string {
            return $invocation->getParameters()[0]->getLoc();
        }, $spy->getInvocations());

        var_dump($urls);
    }
}
