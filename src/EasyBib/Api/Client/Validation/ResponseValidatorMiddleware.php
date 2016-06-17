<?php

namespace EasyBib\Api\Client\Validation;

use Psr\Http\Message\ResponseInterface;

class ResponseValidatorMiddleware
{
    /**
     * @var mixed[]
     */
    private $payload;

    public function __invoke(ResponseInterface $response)
    {
        $this->payload = json_decode($response->getBody(), true);

        $this->checkInfrastructureError($response);
        $this->checkInvalidJson($response);
        $this->checkTokenExpiration();
        $this->checkUnauthorized($response);
        $this->checkNotFoundError($response);
        $this->checkApiError($response);
        $this->checkMiscError($response);

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @throws InvalidJsonException
     */
    private function checkInvalidJson(ResponseInterface $response)
    {
        if (json_last_error() != JSON_ERROR_NONE) {
            $message = sprintf('Invalid JSON: "%s"', $response->getBody());
            throw new InvalidJsonException($message);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws UnauthorizedActionException
     */
    private function checkUnauthorized(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 403) {
            throw new UnauthorizedActionException($this->payload['msg']);
        }
    }

    /**
     * @throws ExpiredTokenException
     */
    private function checkTokenExpiration()
    {
        if (empty($this->payload['error'])) {
            return;
        }

        if ($this->payload['error'] == 'invalid_grant') {
            throw new ExpiredTokenException();
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws ResourceNotFoundException
     */
    private function checkNotFoundError(ResponseInterface $response)
    {
        if (isset($this->payload['msg']) && $this->payload['msg'] == 'Not Found') {
            throw new ResourceNotFoundException(
                $this->payload['msg'],
                $response->getStatusCode()
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws ApiErrorException
     */
    private function checkApiError(ResponseInterface $response)
    {
        if (isset($this->payload['error']) && isset($this->payload['error_description'])) {
            throw new ApiErrorException(
                $this->payload['error_description'],
                $response->getStatusCode()
            );
        }

        if (isset($this->payload['msg'])) {
            throw new ApiErrorException(
                $this->payload['msg'],
                $response->getStatusCode()
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws InfrastructureErrorException
     */
    private function checkInfrastructureError(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();

        if (in_array($statusCode, [502, 503, 504])) {
            throw new InfrastructureErrorException($statusCode);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws ApiErrorException
     */
    private function checkMiscError(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 400) {
            $message = sprintf('Could not complete request: %s', var_export($this->payload, true));
            throw new ApiException($message, 500);
        }
    }
}
