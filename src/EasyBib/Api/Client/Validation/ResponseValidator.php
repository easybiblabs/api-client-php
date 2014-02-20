<?php

namespace EasyBib\Api\Client\Validation;

use Guzzle\Http\Message\Response;

class ResponseValidator
{
    public function validate(Response $response)
    {
        if ($this->isTokenExpired($response)) {
            throw new ExpiredTokenException();
        }

        if ($this->isInvalidJson($response)) {
            $message = sprintf('Invalid JSON: "%s"', $response->getBody(true));
            throw new InvalidJsonException($message);
        }
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function isTokenExpired(Response $response)
    {
        if ($response->getStatusCode() != 400) {
            return false;
        }

        return json_decode($response->getBody(true))->error == 'invalid_grant';
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function isInvalidJson(Response $response)
    {
        json_decode($response->getBody(true));
        return json_last_error() != JSON_ERROR_NONE;
    }
}
