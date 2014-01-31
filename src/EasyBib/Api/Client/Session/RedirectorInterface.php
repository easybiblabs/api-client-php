<?php

namespace EasyBib\Api\Client\Session;

interface RedirectorInterface
{
    /**
     * @param $url
     * @return void
     */
    public function redirect($url);
}
