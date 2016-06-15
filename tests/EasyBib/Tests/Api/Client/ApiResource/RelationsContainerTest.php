<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiResource\Relation;
use EasyBib\Api\Client\ApiResource\RelationsContainer;

class RelationsContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getRelationsData()
    {
        return [
            [
                [
                    (object) [
                        'href' => 'http://api.example.org/foo/bar/',
                        'rel' => 'foo',
                        'type' => 'application/vnd.com.easybib.data+json',
                        'title' => 'Some link',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getRelationsData
     * @param array $relations
     */
    public function testGetAll(array $relations)
    {
        $relationsContainer = new RelationsContainer($relations);

        $relationObjects = array_map(function ($relationData) {
            return new Relation($relationData);
        }, $relations);

        $this->assertEquals($relationObjects, $relationsContainer->getAll());
    }

    /**
     * @dataProvider getRelationsData
     * @param array $relations
     */
    public function testListAll(array $relations)
    {
        $relationsContainer = new RelationsContainer($relations);

        $rels = array_map(function ($relation) {
            return $relation->rel;
        }, $relations);

        $this->assertEquals($rels, $relationsContainer->listAll());
    }

    /**
     * @dataProvider getRelationsData
     * @param array $relations
     */
    public function testContains(array $relations)
    {
        $relationsContainer = new RelationsContainer($relations);

        $this->assertTrue($relationsContainer->contains('foo'));
        $this->assertFalse($relationsContainer->contains('bar'));
    }

    /**
     * @dataProvider getRelationsData
     * @param array $relations
     */
    public function testGet(array $relations)
    {
        $relationsContainer = new RelationsContainer($relations);

        $this->assertEquals(
            new Relation($relations[0]),
            $relationsContainer->get($relations[0]->rel)
        );
    }

    /**
     * @dataProvider getRelationsData
     * @param array $relations
     */
    public function testAdd(array $relations)
    {
        $newRelation = (object) [
            'href' => 'http://foo.example.com/bar',
            'rel' => 'jimmy jim',
        ];

        $relationsContainer = new RelationsContainer($relations);
        $relationsContainer->add($newRelation);

        $this->assertCount(2, $relationsContainer->getAll());
        $this->assertEquals($newRelation->href, $relationsContainer->get('jimmy jim')->getHref());
    }
}
