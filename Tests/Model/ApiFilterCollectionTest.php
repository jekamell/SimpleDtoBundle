<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Model;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollection;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;

/**
 * Class ApiFilterCollectionTest
 * @package Mell\Bundle\SimpleDtoBundle\Tests\Model
 */
class ApiFilterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param ApiFilterCollectionInterface $collection
     * @param \Closure $closure
     * @param ApiFilterCollectionInterface $expected
     * @dataProvider filterProvider
     * @group ApiFilterCollectionFilter
     */
    public function testFilter(ApiFilterCollectionInterface $collection, \Closure $closure, ApiFilterCollectionInterface $expected)
    {
        $this->assertEquals($expected,  $collection->filter($closure));
    }

    /**
     * @param ApiFilterCollectionInterface $collection
     * @param ApiFilterCollectionInterface $expected
     * @dataProvider uniqueProvider
     * @group ApiFilterCollectionUnique
     */
    public function testUnique(ApiFilterCollectionInterface $collection, ApiFilterCollectionInterface $expected)
    {
        $this->assertEquals($expected, $collection->unique());
    }

    /**
     * @return array
     */
    public function filterProvider()
    {
        return [
            [
                new ApiFilterCollection([]),
                function (ApiFilter $filter) {return true;},
                new ApiFilterCollection()
            ],
            [
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0)]),
                function (ApiFilter $filter) {return $filter->getParam() !== 'bar';},
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0)]),
            ],
            [ // filter by param
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('bar', ApiFilter::OPERATION_EQUAL, 0)]),
                function (ApiFilter $filter) {return $filter->getParam() !== 'foo';},
                new ApiFilterCollection([new ApiFilter('bar', ApiFilter::OPERATION_EQUAL, 0)]),
            ],
            [ // filter by operation
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('bar', ApiFilter::OPERATION_NOT_EQUAL, 0)]),
                function (ApiFilter $filter) {return $filter->getOperation() !== ApiFilter::OPERATION_EQUAL;},
                new ApiFilterCollection([new ApiFilter('bar', ApiFilter::OPERATION_NOT_EQUAL, 0)]),
            ],
            [ // filter by value
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('bar', ApiFilter::OPERATION_NOT_EQUAL, 1)]),
                function (ApiFilter $filter) {return $filter->getValue() !== 0;},
                new ApiFilterCollection([new ApiFilter('bar', ApiFilter::OPERATION_NOT_EQUAL, 1)]),
            ]
        ];
    }

    /**
     * @return array
     */
    public function uniqueProvider()
    {
        return [
            [
                new ApiFilterCollection([]),
                new ApiFilterCollection([])
            ],
            [ // different params
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('bar', ApiFilter::OPERATION_EQUAL, 0)]),
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('bar', ApiFilter::OPERATION_EQUAL, 0)]),
            ],
            [ // different operations
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('foo', ApiFilter::OPERATION_NOT_EQUAL, 0)]),
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('foo', ApiFilter::OPERATION_NOT_EQUAL, 0)]),
            ],
            [ // different values
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 1)]),
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 1)]),
            ],
            [ // equal
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0), new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0)]),
                new ApiFilterCollection([new ApiFilter('foo', ApiFilter::OPERATION_EQUAL, 0)]),
            ]
        ];
    }
}
