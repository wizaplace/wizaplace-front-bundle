<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Wizaplace\SDK\Authentication\ApiKey;
use Wizaplace\SDK\User\User as WizaplaceUser;
use Wizaplace\SDK\User\UserAddress;
use Wizaplace\SDK\User\UserService;
use Wizaplace\SDK\User\UserTitle;

class User implements UserInterface, \Serializable
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_VENDOR = 'ROLE_VENDOR';

    /** @var WizaplaceUser */
    private $wizaplaceUser;

    /** @var ApiKey */
    private $apiKey;

    /** @var null|UserService */
    private $userService;

    /** @var bool */
    private $userIsFresh;

    public function __construct(ApiKey $apiKey, WizaplaceUser $user, ?UserService $userService = null)
    {
        $this->wizaplaceUser = $user;
        $this->userIsFresh = true;
        $this->apiKey = $apiKey;
        $this->userService = $userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getRoles(): array
    {
        $roles = [self::ROLE_USER];

        if ($this->getWizaplaceUser()->isVendor()) {
            $roles[] = self::ROLE_VENDOR;
        }

        return $roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->wizaplaceUser->getEmail();
    }

    public function eraseCredentials(): void
    {
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }

    public function serialize()
    {
        return \serialize([
            'apiKey' => $this->apiKey,
            'wUser' => $this->wizaplaceUser,
        ]);
    }

    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);
        $this->apiKey = $data['apiKey'];
        $this->wizaplaceUser = $data['wUser'];
        $this->userIsFresh = false;
    }

    /**
     * @see \Wizaplace\SDK\User\User::getId
     */
    public function getId(): int
    {
        return $this->wizaplaceUser->getId();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getEmail
     */
    public function getEmail(): string
    {
        return $this->wizaplaceUser->getEmail();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getTitle
     */
    public function getTitle(): ?UserTitle
    {
        return $this->wizaplaceUser->getTitle();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getFirstname
     */
    public function getFirstname(): string
    {
        return $this->wizaplaceUser->getFirstname();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getLastname
     */
    public function getLastname(): string
    {
        return $this->wizaplaceUser->getLastname();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getBirthday
     */
    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->wizaplaceUser->getBirthday();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getBillingAddress
     */
    public function getBillingAddress(): ?UserAddress
    {
        return $this->wizaplaceUser->getBillingAddress();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getShippingAddress
     */
    public function getShippingAddress(): ?UserAddress
    {
        return $this->wizaplaceUser->getShippingAddress();
    }

    /**
     * @see \Wizaplace\SDK\User\User::getCompanyId
     */
    public function getCompanyId(): ?int
    {
        return $this->wizaplaceUser->getCompanyId();
    }

    /**
     * @see \Wizaplace\SDK\User\User::isVendor
     */
    public function isVendor(): bool
    {
        return $this->wizaplaceUser->isVendor();
    }

    public function getWizaplaceUser(): WizaplaceUser
    {
        if (!$this->userIsFresh) {
            $this->refreshWizaplaceUser();
        }

        return $this->wizaplaceUser;
    }

    private function refreshWizaplaceUser(): void
    {
        if (is_null($this->userService)) {
            return;
        }
        $this->wizaplaceUser = $this->userService->getProfileFromId($this->wizaplaceUser->getId());
        $this->userIsFresh = true;
    }
}
