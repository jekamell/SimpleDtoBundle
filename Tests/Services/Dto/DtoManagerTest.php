<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DtoManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param User $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param DtoInterface $expected
     * @dataProvider createDtoProvider
     * @group dtoCreate
     */
    public function testCreateDto(User $entity, $dtoType, $group, array $fields, $expected)
    {
        $manager = new DtoManager(
            $this->getDtoValidator(),
            $this->getDtoHelper(),
            $this->getConfigurator(),
            new EventDispatcher()
        );
        $dto = $manager->createDto($entity, $dtoType, $group, $fields, false);
        $this->assertEquals($expected->getRawData(), $dto->getRawData());
    }

    /**
     * @param array $collection
     * @param $dtoType
     * @param $group
     * @param array $fields
     * @param DtoCollectionInterface $expected
     * @dataProvider createDtoCollectionProvider
     * @group dtoCollectionCreate
     */
    public function testCreateDtoCollection(array $collection, $dtoType, $group, array $fields, DtoCollectionInterface $expected)
    {
        $manager = new DtoManager(
            $this->getDtoValidator(),
            $this->getDtoHelper(),
            $this->getConfigurator(),
            new EventDispatcher()
        );
        $dtoCollection = $manager->createDtoCollection($collection, $dtoType, $group, $fields);
        $this->assertEquals(count($expected), $dtoCollection->count());
        foreach ($expected as $i => $dto) {
            /** @var DtoInterface $expectedDto */
            $expectedDto = $expected[$i];
            /** @var DtoInterface $expectedDto */
            $dtoItem = $dtoCollection[$i];
            $this->assertEquals($expectedDto->getRawData(), $dtoItem->getRawData());
        }
    }

    /**
     * @return array
     */
    public function createDtoProvider()
    {
        return [
            // test common dto fields
            [
                $this->generateUser(['email' => 'mail@email.com']),
                'UserDto',
                'read',
                [],
                new Dto(
                    'UserDto',
                    $this->generateUser(['email' => 'mail@email.com']),
                    'read',
                    [
                        'id' => 0,
                        'addressId' => 0,
                        'firstname' => '',
                        'lastname' => '',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'read',
                [],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'read',
                    [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            // test groups
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'create',
                [],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'create',
                    [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'update',
                [],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'update',
                    [
                        'id' => 1,
                        'addressId' => 1,
                        'firstname' => 'Ivan',
                        'lastname' => 'Ivanov',
                        'password' => 'password',
                        'active' => true,
                        'roles' => [],
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            // test fields
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'read',
                ['id', 'email'],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'read',
                    [
                        'id' => 1,
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'read',
                ['id', 'email', 'password'],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'read',
                    [
                        'id' => 1,
                        'email' => 'mail@email.com',
                    ]
                )
            ],
            [
                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                'UserDto',
                'create',
                ['id', 'email', 'password'],
                new Dto(
                    'UserDto',
                    $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
                    'create',
                    [
                        'id' => 1,
                        'email' => 'mail@email.com',
                        'password' => 'password',
                    ]
                )
            ],
        ];
    }

    /**
     * @return array
     */
    public function createDtoCollectionProvider()
    {
        return [
            [ // empty collection
                [],
                'UserDto',
                'read',
                [],
                new DtoCollection('UserDto', [], '_collection', 'read', [])
            ],
            [ // collection with one item
                [$this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe'])],
                'UserDto',
                'read',
                [],
                new DtoCollection(
                    'UserDto',
                    [$this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe'])],
                    '_collection',
                    'read',
                    [
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                            'read',
                            ['id' => 0, 'addressId' => 0, 'email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe', 'active' => true, 'roles' => [], ]
                        )
                    ]
                )
            ],
            [ // few items collection
                [
                    $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                    $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                ],
                'UserDto',
                'read',
                [],
                new DtoCollection(
                    'UserDto',
                    [
                        $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                        $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                    ],
                    '_collection',
                    'read',
                    [
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                            'read',
                            ['id' => 0, 'addressId' => 0, 'email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe', 'active' => true, 'roles' => [], ]
                        ),
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                            'read',
                            ['id' => 0, 'addressId' => 0, 'email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe', 'active' => true, 'roles' => [], ]
                        ),
                    ]
                )
            ],
            [ // collection with fields
                [
                    $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                    $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                ],
                'UserDto',
                'read',
                ['email'],
                new DtoCollection(
                    'UserDto',
                    [
                        $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                        $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                    ],
                    '_collection',
                    'read',
                    [
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                            'read',
                            ['email' => 'john.doe@email.com']
                        ),
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                            'read',
                            ['email' => 'jane.doe@email.com']
                        ),

                    ]
                )
            ],
            [ // groups
                [
                    $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe', 'password' => 'password1']),
                    $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe', 'password' => 'password2']),
                ],
                'UserDto',
                'read',
                [],
                new DtoCollection(
                    'UserDto',
                    [
                        $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe', 'password' => 'password1']),
                        $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe', 'password' => 'password2']),
                    ],
                    '_collection',
                    'read',
                    [
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe']),
                            'read',
                            ['id' => 0, 'addressId' => 0, 'email' => 'john.doe@email.com', 'firstname' => 'John', 'lastname' => 'Doe', 'active' => true, 'roles' => [], ]
                        ),
                        new Dto(
                            'UserDto',
                            $this->generateUser(['email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe']),
                            'read',
                            ['id' => 0, 'addressId' => 0, 'email' => 'jane.doe@email.com', 'firstname' => 'Jane', 'lastname' => 'Doe', 'active' => true, 'roles' => [], ]
                        ),

                    ]
                )
            ]
        ];
    }

    /**
     * @return DtoHelper
     */
    private function getDtoHelper()
    {
        return new DtoHelper(new FileLocator(), __DIR__ . '/' . 'dto.yml', 'Y-m-d', 'c');
    }

    /**
     * @return DtoValidator
     */
    private function getDtoValidator()
    {
        return new DtoValidator($this->getDtoHelper());
    }

    /**
     * @return DtoManagerConfigurator
     */
    private function getConfigurator()
    {
        return new DtoManagerConfigurator('_collection', 'Y-m-d', 'c');
    }

    /**
     * @param array $userParams
     * @param array $addressParams
     * @return User
     */
    private function generateUser(array $userParams, array $addressParams = [])
    {
        /** @var User $user */
        $user = $this->bindObjectParams(new User(), $userParams);
        if ($addressParams) {
            /** @var Address $address */
            $address = $this->bindObjectParams(new Address(), $addressParams);
            $user->setAddress($address);
        }

        return $user;
    }

    /**
     * @param object $object
     * @param array $params
     * @return object
     */
    private function bindObjectParams($object, array $params)
    {
        foreach ($params as $param => $value) {
            call_user_func([$object, $this->getDtoHelper()->getFieldSetter($param)], $value);
        }

        return $object;
    }
}
