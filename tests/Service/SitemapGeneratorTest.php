<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use SitemapGenerator\Sitemap\Sitemap;
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

        self::assertContains('/', $urls); // static URL
        self::assertContains('/a/adidas', $urls); // attribute variant URL
        self::assertContains('/p/it/headsets/casque-corsair-gaming', $urls); // product URL
        self::assertContains('/c/categorie-principale', $urls); // category URL
        self::assertContains('/v/acme', $urls); // company URL
        self::assertContains('/faq', $urls); // CMS page URL
    }
}
