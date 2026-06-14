<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Base controller for all API resources, providing a consistent
 * JSON response envelope: { status, message, data|errors }.
 */
abstract class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected function respondSuccess($data = null, string $message = 'Success', int $code = ResponseInterface::HTTP_OK)
    {
        $body = ['status' => 'success', 'message' => $message];

        if ($data !== null) {
            $body['data'] = $data;
        }

        return $this->respond($body, $code);
    }

    protected function respondCreatedSuccess($data = null, string $message = 'Resource created successfully')
    {
        return $this->respondSuccess($data, $message, ResponseInterface::HTTP_CREATED);
    }

    protected function respondError(string $message, int $code = ResponseInterface::HTTP_BAD_REQUEST, $errors = null)
    {
        $body = ['status' => 'error', 'message' => $message];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return $this->respond($body, $code);
    }

    protected function respondNotFoundError(string $message = 'Resource not found')
    {
        return $this->respondError($message, ResponseInterface::HTTP_NOT_FOUND);
    }

    protected function respondValidationError($errors, string $message = 'Validation failed')
    {
        return $this->respondError($message, ResponseInterface::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
}
