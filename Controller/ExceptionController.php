<?php

namespace Mell\Bundle\SimpleDtoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExceptionController
 * @package Mell\Bundle\SimpleDtoBundle\Controller
 */
class ExceptionController extends Controller
{
    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';
    const ENV_TEST = 'test';
    const UNKNOWN_ERROR_MESSAGE = 'Unknown error';

    /** @var array */
    protected $exceptionCodeMessageMap = [
        Response::HTTP_UNAUTHORIZED => 'Unauthorized',
        Response::HTTP_FORBIDDEN => 'Access denied',
        Response::HTTP_NOT_FOUND => 'Resource not found',
        Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed',
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Server error',
    ];

    /**
     * @param FlattenException $exception
     * @return JsonResponse
     */
    public function showAction(FlattenException $exception)
    {
        $data = [];
        if ($this->getEnvironment() === self::ENV_PROD) {
            if (isset($this->exceptionCodeMessageMap[$exception->getStatusCode()])) {
                $data['_error'] = $this->exceptionCodeMessageMap[$exception->getStatusCode()];
            } else {
                $data['_error'] = self::UNKNOWN_ERROR_MESSAGE;
            }
        } else {
            $data['_error'] = $exception->getMessage();
            if ($this->getDebug()) {
                $data['_class'] = $exception->getClass();
                $data['_file'] = $exception->getFile();
                $data['_line'] = $exception->getLine();
            }
        }

        return new JsonResponse($data, $exception->getStatusCode());
    }

    /**
     * @return string
     */
    private function getEnvironment()
    {
        return $this->getParameter('kernel.environment');
    }

    /**
     * @return mixed
     */
    private function getDebug()
    {
        return $this->getParameter('kernel.debug');
    }
}
