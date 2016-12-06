<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ExceptionListener
 * @package Mell\Bundle\SimpleDtoBundle\EventListener
 */
class ExceptionListener
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
    /** @var string */
    protected $environment;
    /** @var bool */
    protected $debug;

    /**
     * ExceptionListener constructor.
     * @param string $environment
     * @param bool $debug
     */
    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $code = $exception instanceof HttpException ? $exception->getStatusCode() : $exception->getCode();
        $data = [];
        if ($this->getEnvironment() === self::ENV_PROD) {
            if (isset($this->exceptionCodeMessageMap[$code])) {
                $data['_error'] = $this->exceptionCodeMessageMap[$code];
            } else {
                $data['_error'] = self::UNKNOWN_ERROR_MESSAGE;
            }
        } else {
            $data['_error'] = $exception->getMessage();
            if ($this->getDebug()) {
                $data['_file'] = $exception->getFile();
                $data['_line'] = $exception->getLine();
                $data['_trace'] = $exception->getTraceAsString();
            }
        }

        $event->setResponse(new JsonResponse($data, $code ? : JsonResponse::HTTP_INTERNAL_SERVER_ERROR));
    }

    /**
     * @return mixed
     */
    protected function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    protected function getDebug()
    {
        return $this->debug;
    }
}
