<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use WizaplaceFrontBundle\Tests\BundleTestCase;

class AppExtensionTest extends BundleTestCase
{
    public function testFormatPrice(): void
    {
        $container = self::$kernel->getContainer();
        $extension = $container->get('test.WizaplaceFrontBundle\Twig\AppExtension');

        // check edge case 0
        $this->assertSame('<span class="price__integer-part">0</span><span class="price__delimiter">,</span><span class="price__decimal-part">00</span><span class="price__currency"> €</span>', $extension->formatPrice(0.));

        // check edge case negative
        $this->assertSame('<span class="price__integer-part">-1</span><span class="price__delimiter">,</span><span class="price__decimal-part">00</span><span class="price__currency"> €</span>', $extension->formatPrice(-1));

        // check standard case 1
        $this->assertSame('<span class="price__integer-part">1</span><span class="price__delimiter">,</span><span class="price__decimal-part">00</span><span class="price__currency"> €</span>', $extension->formatPrice(1.));

        // check rounding to lower cent
        $this->assertSame('<span class="price__integer-part">3</span><span class="price__delimiter">,</span><span class="price__decimal-part">14</span><span class="price__currency"> €</span>', $extension->formatPrice(3.14159265359));

        // check rounding to higher cent
        $this->assertSame('<span class="price__integer-part">7</span><span class="price__delimiter">,</span><span class="price__decimal-part">78</span><span class="price__currency"> €</span>', $extension->formatPrice(7.777));

        // check standard case with 1 decimal expended to 2
        $this->assertSame('<span class="price__integer-part">3</span><span class="price__delimiter">,</span><span class="price__decimal-part">50</span><span class="price__currency"> €</span>', $extension->formatPrice(3.5));

        // check thousands delimiter
        $this->assertSame('<span class="price__integer-part">1 000</span><span class="price__delimiter">,</span><span class="price__decimal-part">10</span><span class="price__currency"> €</span>', $extension->formatPrice(1000.1));
    }
}
