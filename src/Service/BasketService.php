<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Wizaplace\SDK\Authentication\AuthenticationRequired;
use Wizaplace\SDK\Basket\Basket;
use Wizaplace\SDK\Basket\Comment;
use Wizaplace\SDK\Basket\PaymentInformation;
use Wizaplace\SDK\Basket\SetPickupPointCommand;
use Wizaplace\SDK\Catalog\DeclinationId;
use WizaplaceFrontBundle\Security\User;

/**
 * Wraps {@see \Wizaplace\SDK\Basket\BasketService}, storing the basketID for you.
 */
class BasketService implements EventSubscriberInterface, LogoutHandlerInterface
{
    private const ID_SESSION_KEY = '_basketId';

    /** @var  \Wizaplace\SDK\Basket\BasketService */
    private $baseService;

    /** @var SessionInterface */
    private $session;

    /** @var null|Basket */
    private $basket;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(\Wizaplace\SDK\Basket\BasketService $baseService, SessionInterface $session, ?LoggerInterface $logger = null)
    {
        $this->baseService = $baseService;
        $this->session = $session;

        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }

    public function getBasket(): Basket
    {
        $basketId = $this->getCurrentBasketId();
        if ($basketId === null) {
            return Basket::createEmpty('fake-empty-basket');
        }

        if (!$this->basket || $this->basket->getId() !== $basketId) {
            try {
                $this->basket = $this->baseService->getBasket($basketId);
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    $this->forgetBasket();

                    return $this->getBasket();
                }
            }
        }

        return $this->basket;
    }

    /**
     * @throws \Wizaplace\SDK\Basket\Exception\BadQuantity
     * @throws \Wizaplace\SDK\Exception\NotFound
     */
    public function addProductToBasket(DeclinationId $declinationId, int $quantity): int
    {
        try {
            $result = $this->baseService->addProductToBasket($this->getBasketId(), $declinationId, $quantity);
        } finally {
            $this->basket = null; // invalidate local cache
        }

        return $result;
    }

    /**
     * @throws \Wizaplace\SDK\Exception\NotFound
     */
    public function removeProductFromBasket(DeclinationId $declinationId): void
    {
        try {
            $this->baseService->removeProductFromBasket($this->getBasketId(), $declinationId);
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @throws \Wizaplace\SDK\Exception\NotFound
     */
    public function cleanBasket(): void
    {
        try {
            $this->baseService->cleanBasket($this->getBasketId());
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @throws \Wizaplace\SDK\Basket\Exception\BadQuantity
     * @throws \Wizaplace\SDK\Exception\NotFound
     */
    public function updateProductQuantity(DeclinationId $declinationId, int $quantity): int
    {
        try {
            $result = $this->baseService->updateProductQuantity($this->getBasketId(), $declinationId, $quantity);
        } finally {
            $this->basket = null; // invalidate local cache
        }

        return $result;
    }

    /**
     * @throws \Wizaplace\SDK\Exception\BasketNotFound
     * @throws \Wizaplace\SDK\Exception\CouponCodeAlreadyApplied
     * @throws \Wizaplace\SDK\Exception\CouponCodeDoesNotApply
     */
    public function addCoupon(string $coupon): void
    {
        try {
            $this->baseService->addCoupon($this->getBasketId(), $coupon);
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @throws \Wizaplace\SDK\Basket\Exception\CouponNotInTheBasket
     */
    public function removeCoupon(string $coupon): void
    {
        try {
            $this->baseService->removeCoupon($this->getBasketId(), $coupon);
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @see \Wizaplace\SDK\Basket\BasketService::getPayments
     * @return \Wizaplace\SDK\Basket\Payment[]
     * @throws AuthenticationRequired
     * @throws \Wizaplace\SDK\Exception\NotFound
     */
    public function getPayments(): array
    {
        return $this->baseService->getPayments($this->getBasketId());
    }

    public function selectShippings(array $selections): void
    {
        try {
            $this->baseService->selectShippings($this->getBasketId(), $selections);
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @throws AuthenticationRequired
     * @throws \Wizaplace\SDK\Exception\BasketIsEmpty
     * @throws \Wizaplace\SDK\Exception\BasketNotFound
     * @throws \Wizaplace\SDK\Exception\SomeParametersAreInvalid
     */
    public function checkout(int $paymentId, bool $acceptTerms, string $redirectUrl): PaymentInformation
    {
        try {
            $result = $this->baseService->checkout($this->getBasketId(), $paymentId, $acceptTerms, $redirectUrl);
        } finally {
            $this->basket = null; // invalidate local cache
        }

        return $result;
    }

    public function forgetBasket(): void
    {
        try {
            $this->session->remove(self::ID_SESSION_KEY);
            try {
                $this->baseService->setUserBasketId(null);
            } catch (AuthenticationRequired $e) {
                // We are not logged in, this is not a real error.
            }
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * @param Comment[] $comments
     * @throws \Wizaplace\SDK\Exception\NotFound
     * @throws \Wizaplace\SDK\Exception\SomeParametersAreInvalid
     */
    public function updateComments(array $comments): void
    {
        $this->baseService->updateComments($this->getBasketId(), $comments);
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!($user instanceof User)) {
            return;
        }

        try {
            $userBasketId = $this->baseService->getUserBasketId();

            if ($userBasketId === null) {
                $this->baseService->setUserBasketId($this->getBasketId());

                return;
            }

            $currentBasketId = $this->getCurrentBasketId();
            if (null !== $currentBasketId) {
                if ($currentBasketId === $userBasketId) {
                    return; // can't merge a basket with itself
                }
                $this->baseService->mergeBaskets($userBasketId, $currentBasketId);
            }

            $this->setCurrentBasketId($userBasketId);
        } catch (\Throwable $e) {
            // We just log the exception, we don't want this to cause an error page.
            $this->logger->log(LogLevel::ERROR, 'Failed to load or merge the user\'s basket ID', [
                'exception' => $e,
            ]);
        }
    }

    /**
     * @inheritdoc
     * @uses \WizaplaceFrontBundle\Service\BasketService::onAuthenticationSuccess
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    /**
     * @inheritdoc
     */
    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $this->forgetBasket();
    }

    /**
     * @throws \Wizaplace\SDK\Exception\SomeParametersAreInvalid
     */
    public function setPickupPoint(SetPickupPointCommand $command): void
    {
        $command->setBasketId($this->getBasketId());

        try {
            $this->baseService->setPickupPoint($command);
        } finally {
            $this->basket = null; // invalidate local cache
        }
    }

    /**
     * Gets current basket ID, or create a new one
     * @return string
     */
    private function getBasketId(): string
    {
        $basketId = $this->getCurrentBasketId();

        if (null === $basketId) {
            $this->basket = $this->baseService->createEmptyBasket();
            $basketId = $this->basket->getId();
            $this->setCurrentBasketId($basketId);
        }

        return $basketId;
    }


    /**
     * Gets current basket ID, if it exists.
     * Most of the time you should not use this , {@see \WizaplaceFrontBundle\Service\BasketService::getBasketId} instead.
     * @return null|string
     */
    private function getCurrentBasketId(): ?string
    {
        return $this->session->get(self::ID_SESSION_KEY, null);
    }

    private function setCurrentBasketId(string $basketId): void
    {
        $this->session->set(self::ID_SESSION_KEY, $basketId);
    }
}
