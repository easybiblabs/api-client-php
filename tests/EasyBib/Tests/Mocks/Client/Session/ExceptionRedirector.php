<?php

namespace EasyBib\Tests\Mocks\Client\Session;

use EasyBib\Api\Client\Session\RedirectorInterface;

class ExceptionRedirector implements RedirectorInterface
{
    /**
     * @param string $url
     * @return callable
     */
    public function getCallback($url)
    {
        return function () use ($url) {
            throw new RedirectException('Redirecting to ' . $url);
        };
    }
}
