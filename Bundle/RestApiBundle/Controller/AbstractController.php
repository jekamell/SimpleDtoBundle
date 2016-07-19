<?php

namespace Mell\Bundle\RestApiBundle\Controller;

use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param DtoInterface $data
     * @param int $statusCode
     * @param string $format
     * @return Response
     */
    protected function serializeResponse($data, $statusCode = Response::HTTP_OK, $format = self::FORMAT_JSON)
    {
        return new Response(
            $this->get('serializer')->serialize($data, $format, []),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }
}
