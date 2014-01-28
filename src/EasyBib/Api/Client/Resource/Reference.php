<?php

namespace EasyBib\Api\Client\Resource;

class Reference
{
    /**
     * @var \stdClass
     */
    private $rawData;

    private static $requiredKeys = [
        'href',
        'ref',
        'type',
        'title',
    ];

    /**
     * @param \stdClass $rawData
     */
    public function __construct(\stdClass $rawData)
    {
        self::validate($rawData);

        $this->rawData = $rawData;
    }

    public function getHref()
    {
        return $this->rawData->href;
    }

    public function getRef()
    {
        return $this->rawData->ref;
    }

    public function getType()
    {
        return $this->rawData->type;
    }

    public function getTitle()
    {
        return $this->rawData->title;
    }

    private static function validate(\stdClass $rawData)
    {
        foreach (self::$requiredKeys as $key) {
            if (!isset($rawData->$key)) {
                throw new InvalidResourceLinkException('Missing ' . $key);
            }
        }
    }
}
