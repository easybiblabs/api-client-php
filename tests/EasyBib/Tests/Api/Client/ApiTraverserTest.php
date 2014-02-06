<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ExpiredTokenException;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\Resource\Resource;

class ApiTraverserTest extends TestCase
{
    /**
     * @return array
     */
    public function getValidCitations()
    {
        $citation = [
            'source' => 'book',
            'pubtype' => ['main' => 'pubnonperiodical'],
            'contributors' => [
                [
                    'last' => 'Salinger',
                    'first' => 'J. D.',
                    'function' => 'author',
                ]
            ],
            'pubnonperiodical' => [
                'title' => 'The Catcher in the Rye',
                'publisher' => 'Little, Brown',
                'year' => '1951',
            ]
        ];

        $expectedResponseResource = [
            'links' => [
                [
                    'href' => 'http://example.org/projects/123/citations/456',
                    'rel' => 'me',
                    'type' => 'application/vnd.com.easybib.data+json',
                    'title' => 'The Catcher in the Rye',
                ],
            ],
            'data' => $citation,
        ];

        return [
            [$citation, $expectedResponseResource],
        ];
    }

    public function testGetForCollection()
    {
        $collection = [
            'data' => [
                [
                    'links' => [],
                    'data' => [
                        'href' => 'http://foo.example.com/',
                        'rel' => 'me',
                        'type' => 'text',
                        'title' => 'Bar',
                    ],
                ]
            ],
            'links' => [
            ],
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $collection);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->get('url placeholder');

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($collection, $response);
    }

    public function testGetUser()
    {
        $user = [
            'links' => [],
            'data' => [
                'first' => 'Jim',
                'last' => 'Johnson',
                'email' => 'jj@example.org',
                'role' => 'mybib',
            ]
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $user);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->getUser();

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedAResource($user, $response);
    }

    public function testGetCitationsReturnsCollection()
    {
        $collection = [
            'links' => [],
            'data' => [
                [
                    'links' => [],
                    'data' => [
                        'source' => 'book',
                        'pubtype' => ['main' => 'pubnonperiodical'],
                    ],
                ],
            ]
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $collection);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->get('citations');

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($collection, $response);
    }

    public function testGetPassesTokenInHeaderWithJwt()
    {
        $accessToken = 'ABC123';

        $this->given->iHaveRegisteredWithAJwtSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $this->history->getLastRequest()->getHeader('Authorization')
                ->hasValue('Bearer ' . $accessToken)
        );
    }

    public function testGetPassesTokenInHeaderWithAuthCodeGrant()
    {
        $accessToken = 'ABC123';

        $this->given->iHaveRegisteredWithAnAuthCodeSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $this->history->getLastRequest()->getHeader('Authorization')
                ->hasValue('Bearer ' . $accessToken)
        );
    }

    public function testGetWithExpiredToken()
    {
        $this->given->iAmReadyToReturnAnExpiredTokenError($this->mockResponses);

        $this->setExpectedException(ExpiredTokenException::class);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');
    }

    /**
     * @dataProvider getValidCitations
     */
    public function testPost(array $citation, array $expectedResponseResource)
    {
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $expectedResponseResource);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->post('/projects/123/citations', $citation);

        $this->shouldHaveMadeAnApiRequest('POST');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    /**
     * @dataProvider getValidCitations
     */
    public function testPut(array $citation, array $expectedResponseResource)
    {
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $expectedResponseResource);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->put('/projects/123/citations/456', $citation);

        $this->shouldHaveMadeAnApiRequest('PUT');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    public function testDelete()
    {
        $expectedResource = [
            'data' => [],
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $api = new ApiTraverser($this->httpClient);
        $response = $api->delete('/projects/123/citations/456');

        $this->shouldHaveMadeAnApiRequest('DELETE');
        $this->shouldHaveReturnedADeletedResource($expectedResource, $response);
    }

    private function shouldHaveMadeAnApiRequest($httpMethod)
    {
        $lastRequest = $this->history->getLastRequest();

        $this->assertEquals($httpMethod, $lastRequest->getMethod());

        $this->assertTrue(
            $lastRequest->getHeader('Accept')
                ->hasValue('application/vnd.com.easybib.data+json')
        );
    }

    private function shouldHaveReturnedAResource(
        array $expectedResponseArray,
        Resource $resource
    ) {
        $this->assertSameData($expectedResponseArray, $resource);

        $this->assertEquals(
            $this->extractReferences($expectedResponseArray['links']),
            $resource->getResponseDataContainer()->getReferences()
        );
    }

    private function shouldHaveReturnedADeletedResource(
        array $expectedResponseArray,
        Resource $resource
    ) {
        $this->assertSameData($expectedResponseArray, $resource);
    }

    private function shouldHaveReturnedACollection(
        array $expectedResponseArray,
        Collection $collection
    ) {
        $this->assertEquals(count($expectedResponseArray['data']), count($collection));
    }

    private function recursiveCastObject(array $array)
    {
        return json_decode(json_encode($array));
    }

    private function extractReferences(array $links)
    {
        return array_map(
            function ($reference) {
                return new Reference($this->recursiveCastObject($reference));
            },
            $links
        );
    }

    /**
     * @param array $expectedResponseArray
     * @param Resource $resource
     */
    private function assertSameData(array $expectedResponseArray, Resource $resource)
    {
        $this->assertEquals(
            $this->recursiveCastObject($expectedResponseArray['data']),
            $resource->getResponseDataContainer()->getData()
        );
    }
}
