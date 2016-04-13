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

    /**
     * @return Relation[]
     */
    public function getAll()
    {
        return $this->relations;
    }

    /**
     * @return string[]
     */
    public function listAll()
    {
        return array_map(function ($relation) {
            return $relation->getRel();
        }, $this->relations);
    }

    /**
     * @param string $rel
     * @return bool
     */
    public function contains($rel)
    {
        return in_array($rel, $this->listAll());
    }

    /**
     * @param string $rel
     * @return Relation
     */
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

    /**
     * @param \stdClass $data
     */
    public function add(\stdClass $data)
    {
        $this->relations[] = new Relation($data);
    }
}
