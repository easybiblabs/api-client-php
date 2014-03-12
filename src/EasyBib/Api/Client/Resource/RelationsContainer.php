<?php

namespace EasyBib\Api\Client\Resource;

class RelationsContainer
{
    /**
     * @var \stdData[]
     */
    private $rawLinks;

    /**
     * @param \stdData[] $rawLinks
     */
    public function __construct(array $rawLinks)
    {
        $this->rawLinks = $rawLinks;
    }

    public function getAll()
    {
        return array_map(function ($relationData) {
            return new Relation($relationData);
        }, $this->rawLinks);
    }

    public function listAll()
    {
        return array_map(function ($relation) {
            return $relation->rel;
        }, $this->rawLinks);
    }

    public function contains($rel)
    {
        return in_array($rel, $this->listAll());
    }

    public function get($rel)
    {
        return array_reduce(
            $this->getAll(),
            function ($carry, $currentRelation) use ($rel) {
                if ($currentRelation->getRel() == $rel) {
                    return $currentRelation;
                }

                return $carry;
            },
            null
        );
    }

    public function add(\stdClass $data)
    {
        $this->rawLinks[] = $data;
    }
}
