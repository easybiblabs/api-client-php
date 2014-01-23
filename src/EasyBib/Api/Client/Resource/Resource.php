<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\ResponseDataContainer;

class Resource implements LinkSourceInterface
{
    /**
     * @var ResponseDataContainer
     */
    private $container;

    /**
     * @var \EasyBib\Api\Client\ApiSession
     */
    private $apiSession;

    public function __construct(ResponseDataContainer $container, ApiSession $apiSession)
    {
        $this->container = $container;
        $this->apiSession = $apiSession;
    }

    /**
     * @param string $ref
     * @return LinkSourceInterface
     */
    public function get($ref)
    {
        $link = $this->findLink($ref);

        if (!$link) {
            return null;
        }

        $response = $this->apiSession->get($link->getHref());
        $responseContainer = ResponseDataContainer::fromResponse($response);

        return new Resource($responseContainer, $this->apiSession);
    }

    /**
     * Allows retrieval of the URL; useful e.g. when GETting exported
     * documents
     *
     * @param string $ref
     * @return ResourceLink
     */
    public function findLink($ref)
    {
        foreach ($this->container->getLinks() as $link) {
            if ($link->getRef() == $ref) {
                return $link;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->container->getData()->$name;
    }
}
