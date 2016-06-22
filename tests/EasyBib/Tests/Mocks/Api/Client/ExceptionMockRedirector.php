<?php

namespace EasyBib\Tests\Mocks\Api\Client;

use EasyBib\OAuth2\Client\AuthorizationCodeGrant\RedirectorInterface;

class ExceptionMockRedirector implements RedirectorInterface
{
    /**
     * @param string $url
     * @throws MockRedirectException
     */
    public function redirect($url)
    {
        throw new MockRedirectException('Redirecting to ' . $url);
    }
}
