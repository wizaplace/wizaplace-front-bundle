<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   FriendsOfSymfony <http://friendsofsymfony.github.com/>
 * @license     MIT
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface LoginManagerInterface
{
    public function logInUser(string $firewallName, UserInterface $user);
}
