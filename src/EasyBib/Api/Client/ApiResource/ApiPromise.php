<?php

namespace EasyBib\Api\Client\ApiResource;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class ApiPromise implements PromiseInterface
{
    /** @var PromiseInterface */
    private $httpPromise;

    /** @var ResourceFactory */
    private $resourceFactory;

    public function __construct(PromiseInterface $httpPromise, ResourceFactory $resourceFactory)
    {
        $this->httpPromise = $httpPromise;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            $this->httpPromise = $this->httpPromise->then();

            return $this;
        }

        $this->httpPromise = $this->httpPromise->then(function (ResponseInterface $res) use ($onFulfilled) {
            $onFulfilled($this->resourceFactory->createFromResponse($res));
        }, $onRejected);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function otherwise(callable $onRejected)
    {
        return $this->then(null, $onRejected);
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->httpPromise->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($value)
    {
        $this->httpPromise->resolve($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reject($reason)
    {
        $this->httpPromise->reject($reason);
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        $this->httpPromise->cancel();
    }

    /**
     * @param bool $unwrap
     * @return mixed|ApiResource|Collection
     */
    public function wait($unwrap = true)
    {
        $response = $this->httpPromise->wait($unwrap);
        if ($response instanceof ResponseInterface) {
            $response = $this->resourceFactory->createFromResponse($response);
        }

        return $response;
    }
}
