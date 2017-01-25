<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Psr\Log\LoggerInterface;
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
    const SERVER_ERROR_MESSAGE = 'Server error';

    /** @var array */
    protected $exceptionCodeMessageMap = [
        Response::HTTP_UNAUTHORIZED => 'Unauthorized',
        Response::HTTP_FORBIDDEN => 'Access denied',
        Response::HTTP_NOT_FOUND => 'Resource not found',
        Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed',
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Server error',
    ];
    /** @var LoggerInterface */
    protected $logger;
    /** @var string */
    protected $environment;
    /** @var bool */
    protected $debug;

    /**
     * ExceptionListener constructor.
     * @param LoggerInterface $logger
     * @param param string $environment
     * @param param bool $debug
     */
    public function __construct(LoggerInterface $logger, $environment, $debug)
    {
        $this->logger = $logger;
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $code = $exception instanceof HttpException
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
        $data = [];
        if ($this->getEnvironment() === self::ENV_PROD) {
            if (isset($this->exceptionCodeMessageMap[$code])) {
                $data['_error'] = $this->exceptionCodeMessageMap[$code];
            } else {
                $data['_error'] = self::SERVER_ERROR_MESSAGE;
            }
        } else {
            $data['_error'] = $exception->getMessage();
            if ($this->getDebug()) {
                $data['_file'] = $exception->getFile();
                $data['_line'] = $exception->getLine();
                $data['_trace'] = $exception->getTraceAsString();
            }
        }

        if ($code === Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->critical($exception->getMessage());
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
