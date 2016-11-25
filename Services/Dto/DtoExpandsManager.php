<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class DtoExpandsManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoExpandsManager
{
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var ContainerInterface */
    protected $container;

    /**
     * DtoExpandsManager constructor.
     * @param DtoHelper $dtoHelper
     * @param ContainerInterface $container
     */
    public function __construct(DtoHelper $dtoHelper, ContainerInterface $container)
    {
        $this->dtoHelper = $dtoHelper;
        $this->container = $container;
    }

    /**
     * @param DtoInterface $dto
     * @param array $expands
     * @param array $config
     * @return DtoInterface
     */
    public function processExpands(DtoInterface $dto, array $expands, array $config)
    {
        $data = [];
        foreach ($expands as $expand => $fields) {
            if (!isset($config[$dto->getType()]['expands'][$expand])) {
                throw new BadRequestHttpException(sprintf('Required expand not found: %s', $expand));
            }
            $expandConfig = $config[$dto->getType()]['expands'][$expand];
            $getter = !empty($expandConfig['getter'])
                ? $expandConfig['getter']
                : $this->dtoHelper->getFieldGetter($expand);
            if (is_array($getter)) {
                $expandObject = $this->getExpandFromRepository($dto->getOriginalData(), $getter);
            } else {
                $expandObject = $this->getExpandFromObject($dto->getOriginalData(), $getter);
            }
            if (!$expandObject) {
                continue;
            }
            if (is_array($expandObject) || $expandObject instanceof \ArrayAccess) {
                $expandsCollection = $this->createDtoCollection(
                    $expandObject,
                    $expandConfig['type'],
                    $config,
                    $fields,
                    $dto->getGroup()
                );
                $data['_expands'][$expand] = $expandsCollection;
            } else {
                $expandObject = $this->createDto(
                    $expandObject,
                    $expandConfig['type'],
                    $fields,
                    $config,
                    $dto->getGroup()
                );
                $data['_expands'][$expand] = $expandObject;
            }
        }

        return $dto->append($data);
    }

    /**
     * @param object $entity
     * @param string $type
     * @param array $fields
     * @param array $config
     * @param string $group
     * @return Dto
     */
    protected function createDto($entity, $type, array $fields, array $config, $group)
    {
        $dto = new Dto($type, $entity, []);
        $config = $config[$type];
        /** @var array $options */
        foreach ($config['fields'] as $field => $options) {
            // field was not required (@see dtoManager::getRequiredFields)
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            // field is not allowed for specified group
            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
                continue;
            }

            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$entity, $getter]);
            $data[$field] = $this->dtoHelper->castValueType($options['type'], $value);
            $dto->append([$field => $value]);
        }

        return $dto;
    }

    /**
     * @param array $collection
     * @param string $type
     * @param array $config
     * @param array $fields
     * @param string $group
     * @return DtoCollection
     */
    protected function createDtoCollection($collection, $type, array $config, array $fields, $group)
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->createDto($item, $type, $fields, $config, $group);
        }

        return new DtoCollection($type, $collection, false, $group, $data);
    }

    /**
     * @param object $object
     * @param string $getter
     * @return mixed
     */
    private function getExpandFromObject($object, $getter)
    {
        return call_user_func([$object, $getter]);
    }

    /**
     * @param object $object
     * @param array $getterOptions
     * @return mixed
     * @throws \Exception
     */
    private function getExpandFromRepository($object, array $getterOptions)
    {
        if (!isset($getterOptions['repository'], $getterOptions['method'])) {
            throw new \Exception('"repository" and "method" should be defined');
        }

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository($getterOptions['repository']);
        $method = $getterOptions['method'];
        $arguments = [];
        if (!empty($getterOptions['arguments'])) {
            foreach ($getterOptions['arguments'] as $argument) {
                if ($argument === 'object') { // pass object as one of method params
                    $arguments[] = $object;
                } elseif (substr($argument, 0, 1) === '@') { // use service as argument
                    $arguments[] = $this->container->get(substr($argument, 1));
                } elseif (substr($argument, 0, 1) === '%' && substr($argument, -1) === '%') { // use param as argument
                    $arguments[] = $this->container->getParameter(trim($argument, '%'));
                } else { // simple string injection
                    $arguments[] = $argument;
                }
            }
        }

        return call_user_func_array([$repository, $method], $arguments);
    }
}
