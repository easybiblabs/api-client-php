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
     * @var array
     */
    private $payload;

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
        $this->checkMiscError();
    }

    /**
     * @throws InvalidJsonException
     */
    private function checkInvalidJson()
    {
        $body = $this->response->getBody(true);
        json_decode($body, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $message = sprintf('Invalid JSON: "%s"', $body);
            throw new InvalidJsonException($message);
        }
    }

    /**
     * @throws ExpiredTokenException
     */
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

    /**
     * @throws ApiErrorException
     */
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
     * @throws MiscApiException
     */
    private function checkMiscError()
    {
        if ($this->response->isError()) {
            $message = sprintf('Could not complete request: %s', var_export($this->getPayload(), true));
            throw new MiscApiException($message, 500);
        }
    }

    /**
     * @return array
     */
    private function getPayload()
    {
        if ($this->payload) {
            return $this->payload;
        }

        $body = $this->response->getBody(true);

        return $this->payload = json_decode($body, true);
    }
}
