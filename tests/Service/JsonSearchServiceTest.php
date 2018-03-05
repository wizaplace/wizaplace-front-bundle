<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use WizaplaceFrontBundle\Tests\BundleTestCase;

class JsonSearchServiceTest extends BundleTestCase
{
    public function test(): void
    {
        $container = self::$kernel->getContainer();
        $result = $container->get('test.WizaplaceFrontBundle\Service\JsonSearchService')->search();

        self::assertInternalType('string', $result);
        self::assertNotContains('{}', $result, 'an empty JSON object probably means a PHP object is not properly serialized');

        $decodedResult = json_decode($result);
        self::assertSame(JSON_ERROR_NONE, json_last_error());
        self::assertInstanceOf(\stdClass::class, $decodedResult);
    }
}
