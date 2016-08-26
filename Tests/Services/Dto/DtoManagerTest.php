<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoValidator;
use Symfony\Component\Config\FileLocator;

class DtoManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param User $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param array $expands
     * @param DtoInterface $expected
     * @dataProvider createDtoProvider
     * @group dtoCreate
     */
    public function testCreateDto(User $entity, $dtoType, $group, array $fields, array $expands, $expected)
    {
        $manager = new DtoManager($this->getDtoValidator(), $this->getDtoHelper(), $this->getConfigurator());
        $dto = $manager->createDto($entity, $dtoType, $group, $fields, $expands);
        $this->assertEquals($expected->getRawData(), $dto->getRawData());
    }

    /**
     * @return array
     */
    public function createDtoProvider()
    {
        $now = new \DateTime();

        return [
            // test common dto fields
//            [
//                $this->generateUser(['email' => 'mail@email.com']),
//                'UserDto',
//                'read',
//                [],
//                [],
//                new Dto(
//                    [
//                        'id' => 0,
//                        'addressId' => 0,
//                        'firstname' => '',
//                        'lastname' => '',
//                        'active' => true,
//                        'roles' => [],
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'read',
//                [],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'addressId' => 1,
//                        'firstname' => 'Ivan',
//                        'lastname' => 'Ivanov',
//                        'active' => true,
//                        'roles' => [],
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
            // test groups
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'create',
//                [],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'addressId' => 1,
//                        'firstname' => 'Ivan',
//                        'lastname' => 'Ivanov',
//                        'password' => 'password',
//                        'active' => true,
//                        'roles' => [],
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'update',
//                [],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'addressId' => 1,
//                        'firstname' => 'Ivan',
//                        'lastname' => 'Ivanov',
//                        'password' => 'password',
//                        'active' => true,
//                        'roles' => [],
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
            // test fields
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'read',
//                ['id', 'email'],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'read',
//                ['id', 'email', 'password'],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'email' => 'mail@email.com',
//                    ]
//                )
//            ],
//            [
//                $this->generateUser(['id' => 1, 'addressId' => 1, 'email' => 'mail@email.com', 'firstname' => 'Ivan', 'lastname' => 'Ivanov', 'password' => 'password']),
//                'UserDto',
//                'create',
//                ['id', 'email', 'password'],
//                [],
//                new Dto(
//                    [
//                        'id' => 1,
//                        'email' => 'mail@email.com',
//                        'password' => 'password',
//                    ]
//                )
//            ],
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
