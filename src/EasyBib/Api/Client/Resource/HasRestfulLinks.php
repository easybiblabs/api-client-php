<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ResponseDataContainer;

trait HasRestfulLinks
{
    /**
     * Convenience method to follow RESTful links
     *
     * @param string $ref
     * @return HasRestfulLinks
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
}
