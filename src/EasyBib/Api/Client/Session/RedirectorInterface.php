<?php

namespace EasyBib\Api\Client\Session;

interface RedirectorInterface
{
    /**
     * @return callable
     */
    public function getCallback();
}
