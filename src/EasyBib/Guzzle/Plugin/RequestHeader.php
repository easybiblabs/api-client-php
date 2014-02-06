<?php

namespace EasyBib\Guzzle\Plugin;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BearerAuth
 * @link https://github.com/fkooman/guzzle-bearer-auth-plugin
 * @package EasyBib\Guzzle\Plugin\BearerAuth
 */
class RequestHeader implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * @var string
     */
    private $headerValue;

    /**
     * @param string $headerName
     * @param string $headerValue
     */
    public function __construct($headerName, $headerValue)
    {
        $this->headerName = $headerName;
        $this->headerValue = $headerValue;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'request.before_send' => 'onRequestBeforeSend',
        ];
    }

    /**
     * @param Event $event
     */
    public function onRequestBeforeSend(Event $event)
    {
        $event['request']->setHeader($this->headerName, $this->headerValue);
    }
}
