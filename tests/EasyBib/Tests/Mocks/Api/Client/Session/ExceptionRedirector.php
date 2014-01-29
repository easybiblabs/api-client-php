<?php

namespace EasyBib\Tests\Mocks\Api\Client\Session;

use EasyBib\Api\Client\Session\RedirectorInterface;
use EasyBib\Tests\Mocks\Api\Client\Session\RedirectException;

class ExceptionRedirector implements RedirectorInterface
{
    /**
     * @param string $url
     * @throws RedirectException
     * @return void
     */
    public function redirect($url)
    {
        throw new RedirectException('Redirecting to ' . $url);
    }
}
