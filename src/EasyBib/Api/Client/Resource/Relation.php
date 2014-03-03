<?php

namespace EasyBib\Api\Client\Resource;

class Relation
{
    /**
     * @var \stdClass
     */
    private $rawData;

    /**
     * @var array
     */
    private static $requiredKeys = [
        'href',
        'rel',
    ];

    /**
     * @param \stdClass $rawData
     */
    public function __construct(\stdClass $rawData)
    {
        self::validate($rawData);

        $this->rawData = $rawData;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->rawData->href;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rawData->rel;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->rawData->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->rawData->title;
    }

    /**
     * @param \stdClass $rawData
     * @throws InvalidResourceLinkException
     */
    private static function validate(\stdClass $rawData)
    {
        foreach (self::$requiredKeys as $key) {
            if (!isset($rawData->$key)) {
                throw new InvalidResourceLinkException('Missing ' . $key);
            }
        }
    }
}
