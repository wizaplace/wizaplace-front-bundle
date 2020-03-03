<?php

/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Wizaplace\SDK\Catalog\DeclinationId;
use WizaplaceFrontBundle\Tests\TestEnv\TestKernel;

class RouterDecoratorTest extends KernelTestCase
{
    protected static $class = TestKernel::class;

    public function testDeclinationIdsCanBeUsedInURLQuery()
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $url = $container->get('router')->generate(
            'home',
            [
                'd' => new DeclinationId('1_0'),
            ]
        );

        self::assertSame('/?d=1_0', $url);
    }
}
