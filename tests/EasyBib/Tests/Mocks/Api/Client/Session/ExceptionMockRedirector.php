<?php

namespace EasyBib\Tests\Mocks\Api\Client\Session;

use EasyBib\Api\Client\Session\RedirectorInterface;

class ExceptionMockRedirector implements RedirectorInterface
{
    /**
     * @param string $url
     * @throws MockRedirectException
     * @return void
     */
    public function redirect($url)
    {
        throw new MockRedirectException('Redirecting to ' . $url);
    }
}
