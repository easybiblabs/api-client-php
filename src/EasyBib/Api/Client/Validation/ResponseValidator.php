<?php

namespace EasyBib\Api\Client\Validation;

use Guzzle\Http\Message\Response;

class ResponseValidator
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function validate()
    {
        $this->checkInvalidJson();
        $this->checkTokenExpiration();
        $this->checkApiError();
    }

    private function checkInvalidJson()
    {
        $body = $this->response->getBody(true);
        json_decode($body, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $message = sprintf('Invalid JSON: "%s"', $body);
            throw new InvalidJsonException($message);
        }
    }

    private function checkTokenExpiration()
    {
        $payload = $this->getPayload();

        if (empty($payload['error'])) {
            return;
        }

        if ($payload['error'] == 'invalid_grant') {
            throw new ExpiredTokenException();
        }
    }

    private function checkApiError()
    {
        $payload = $this->getPayload();

        if (isset($payload['error']) && isset($payload['error_description'])) {
            throw new ApiErrorException(
                $payload['error_description'],
                $this->response->getStatusCode()
            );
        }

        if (isset($payload['msg'])) {
            throw new ApiErrorException(
                $payload['msg'],
                $this->response->getStatusCode()
            );
        }
    }

    /**
     * @return array
     */
    private function getPayload()
    {
        $body = $this->response->getBody(true);
        return json_decode($body, true);
    }
}
