<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ResourceDataContainer;

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
        $link = $this->findReference($ref);

        if (!$link) {
            return null;
        }

        return $this->getApiTraverser()->get($link->getHref());
    }

    /**
     * Allows retrieval of the URL; useful e.g. when GETting exported
     * documents
     *
     * @param string $ref
     * @return Reference
     */
    public function findReference($ref)
    {
        foreach ($this->getResponseDataContainer()->getReferences() as $reference) {
            if ($reference->getRel() == $ref) {
                return $reference;
            }
        }

        return null;
    }

    /**
     * @return \EasyBib\Api\Client\ApiTraverser
     */
    abstract public function getApiTraverser();

    /**
     * @return \EasyBib\Api\Client\ResourceDataContainer
     */
    abstract public function getResponseDataContainer();
}
