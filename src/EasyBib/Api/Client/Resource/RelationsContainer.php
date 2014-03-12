<?php

namespace EasyBib\Api\Client\Resource;

class RelationsContainer
{
    /**
     * @var Relation[]
     */
    private $relations;

    /**
     * @param \stdData[] $rawLinks
     */
    public function __construct(array $rawLinks)
    {
        $this->relations = array_map(function ($relationData) {
            return new Relation($relationData);
        }, $rawLinks);
    }

    public function getAll()
    {
        return $this->relations;
    }

    public function listAll()
    {
        return array_map(function ($relation) {
            return $relation->getRel();
        }, $this->relations);
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
        $this->relations[] = new Relation($data);
    }
}
