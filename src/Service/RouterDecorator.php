<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RouterDecorator implements RouterInterface
{
    /** @var RouterInterface */
    private $decorated;

    public function __construct(RouterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @inheritdoc
     */
    public function setContext(RequestContext $context): void
    {
        $this->decorated->setContext($context);
    }

    /**
     * @inheritdoc
     */
    public function getContext(): RequestContext
    {
        return $this->decorated->getContext();
    }

    /**
     * @inheritdoc
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH): string
    {
        $parameters = self::stringifyRecursively($parameters);

        return $this->decorated->generate($name, $parameters, $referenceType);
    }

    /**
     * @inheritdoc
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->decorated->getRouteCollection();
    }

    /**
     * @inheritdoc
     */
    public function match($pathinfo): array
    {
        return $this->decorated->match($pathinfo);
    }

    public function __call(string $name, array $arguments)
    {
        // forwards all unknown methods calls to decorated object
        return call_user_func_array([$this->decorated, $name], $arguments);
    }

    private static function stringifyRecursively($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'stringifyRecursively'], $value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        }

        return $value;
    }
}
