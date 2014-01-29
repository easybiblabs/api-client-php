<?php

namespace EasyBib\Tests\Mocks\Client\Session;

use EasyBib\Api\Client\Session\RedirectorInterface;

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
