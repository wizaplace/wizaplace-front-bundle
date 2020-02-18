<?php

/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class OauthControllerTest extends BundleTestCase
{
    public function testAuthorize()
    {
        $this->client->request('GET', '/oauth-authorize');
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
    }

    public function testLogin()
    {
        // Without code
        $this->client->request('GET', '/oauth');
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->assertSame('/', $this->client->getResponse()->headers->get('Location'));

        // With code
        $this->client->request('GET', '/oauth?code=J-Cxxm4_50d52xjnrzsXIfE6__Q8KHiUjQ60Hn_51Dk=.1531989104656.7aLjcTzCid_sjPdlwdEWnwTH64l84uDNH2XBwZeIiiM=');
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->assertSame('/', $this->client->getResponse()->headers->get('Location'));
    }
}
