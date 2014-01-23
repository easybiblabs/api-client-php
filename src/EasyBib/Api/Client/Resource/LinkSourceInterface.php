<?php

namespace EasyBib\Api\Client\Resource;

interface LinkSourceInterface
{
    /**
     * Convenience method to follow RESTful links
     *
     * @param string $ref
     * @return LinkSourceInterface
     */
    public function get($ref);

    /**
     * Allows retrieval of the URL; useful e.g. when GETting exported
     * documents
     *
     * @param string $ref
     * @return ResourceLink
     */
    public function findLink($ref);
}
