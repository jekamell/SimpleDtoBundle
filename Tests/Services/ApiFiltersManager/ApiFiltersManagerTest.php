<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Services\ApiFiltersManager;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollection;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFiltersManager;
use Doctrine\Common\Collections\Criteria;

/**
 * Class ApiFiltersManagerTest
 * @package Mell\Bundle\SimpleDtoBundle\Tests\Services\ApiFiltersManager
 */
class ApiFiltersManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $filterStr
     * @param ApiFilterCollectionInterface $expected
     * @dataProvider parseProvider
     * @group apiFiltersParse
     */
    public function testParse($filterStr, ApiFilterCollectionInterface $expected)
    {
        $manager = new ApiFiltersManager();
        $filters = $manager->parse($filterStr);
        $this->assertEquals($expected, $filters);
    }

    /**
     * @return array
     */
    public function parseProvider()
    {
        return [
            /** empty filters string */
            [
                '',
                new ApiFilterCollection()
            ],
            [
                null,
                new ApiFilterCollection()
            ],
            /** equals */
            [
                'firstname:John',
                new ApiFilterCollection([new ApiFilter('firstname', ApiFilter::OPERATION_EQUAL, 'John')])
            ],
            [
                'firstname:John|lastname:Doe',
                new ApiFilterCollection(
                    [
                        new ApiFilter('firstname', ApiFilter::OPERATION_EQUAL, 'John'),
                        new ApiFilter('lastname', ApiFilter::OPERATION_EQUAL, 'Doe')
                    ]
                )
            ],
            [
                'content:list:item1,item2',
                new ApiFilterCollection([new ApiFilter('content', ApiFilter::OPERATION_EQUAL, 'list:item1,item2')])
            ],
            [
                'content:list:item1,item2|title:subject',
                new ApiFilterCollection(
                    [
                        new ApiFilter('content', ApiFilter::OPERATION_EQUAL, 'list:item1,item2'),
                        new ApiFilter('title', ApiFilter::OPERATION_EQUAL, 'subject'),
                    ]
                )
            ],
            /** not equals */
            [
                'firstname!:John',
                new ApiFilterCollection([new ApiFilter('firstname', ApiFilter::OPERATION_NOT_EQUAL, 'John')])
            ],
            [
                'firstname!:John|lastname!:Doe',
                new ApiFilterCollection(
                    [
                        new ApiFilter('firstname', ApiFilter::OPERATION_NOT_EQUAL, 'John'),
                        new ApiFilter('lastname', ApiFilter::OPERATION_NOT_EQUAL, 'Doe'),
                    ]
                )
            ],
            [
                'content!:list:item1,item2|description!:!:',
                new ApiFilterCollection(
                    [
                        new ApiFilter('content', ApiFilter::OPERATION_NOT_EQUAL, 'list:item1,item2'),
                        new ApiFilter('description', ApiFilter::OPERATION_NOT_EQUAL, '!:'),
                    ]
                )
            ],
            /** less then */
            [
                'age<:25',
                new ApiFilterCollection([new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 25)])
            ],
            [
                'age<:25|postCount<:15',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_LESS_THEN, 15),
                    ]
                )
            ],
            [
                'age<:25|postCount<:<body>',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_LESS_THEN, '<body>'),
                    ]
                )
            ],
            /** more then */
            [
                'age>:25',
                new ApiFilterCollection([new ApiFilter('age', ApiFilter::OPERATION_MORE_THEN, 25)])
            ],
            [
                'age>:25|postCount>:15',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_MORE_THEN, 15),
                    ]
                )
            ],
            [
                'age>:25|postCount>:<body>',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_MORE_THEN, '<body>'),
                    ]
                )
            ],
            /** less or equal to */
            [
                'age<=:25',
                new ApiFilterCollection([new ApiFilter('age', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, 25)])
            ],
            [
                'age<=:25|postCount<=:15',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, 15),
                    ]
                )
            ],
            [
                'age<=:25|postCount<=:<body>',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, '<body>'),
                    ]
                )
            ],
            /** more or equal to */
            [
                'age>=:25',
                new ApiFilterCollection([new ApiFilter('age', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, 25)])
            ],
            [
                'age>=:25|postCount>=:15',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, 15),
                    ]
                )
            ],
            [
                'age>=:25|postCount>=:<body>',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, '<body>'),
                    ]
                )
            ],
            /** in array */
            [
                'firstname:(John,Jane)',
                new ApiFilterCollection([new ApiFilter('firstname', ApiFilter::OPERATION_IN_ARRAY, ['John', 'Jane'])])
            ],
            [
                'firstname:(John, Jane)', // space(s) before Jane
                new ApiFilterCollection([new ApiFilter('firstname', ApiFilter::OPERATION_IN_ARRAY, ['John', ' Jane'])])
            ],
            [
                'firstname:(John,Jane)|lastname:(Doe)',
                new ApiFilterCollection(
                    [
                        new ApiFilter('firstname', ApiFilter::OPERATION_IN_ARRAY, ['John', 'Jane']),
                        new ApiFilter('lastname', ApiFilter::OPERATION_IN_ARRAY, ['Doe']),
                    ]
                )
            ],
            /** not in array */
            [
                'firstname!:(John,Jane)',
                new ApiFilterCollection([new ApiFilter('firstname', ApiFilter::OPERATION_NOT_IN_ARRAY, ['John', 'Jane'])])
            ],
            [
                'firstname!:(John,Jane)|lastname!:(Doe)',
                new ApiFilterCollection(
                    [
                        new ApiFilter('firstname', ApiFilter::OPERATION_NOT_IN_ARRAY, ['John', 'Jane']),
                        new ApiFilter('lastname', ApiFilter::OPERATION_NOT_IN_ARRAY, ['Doe']),
                    ]
                )
            ],
            /** range */
            [
                'age>:15|age<:25',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_THEN, 15),
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 25),
                    ]
                )
            ],
            /** duplicated values */
            [
                'age<:15|age<:20',
                new ApiFilterCollection(
                    [
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 15),
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 20),
                    ]
                )
            ],
            /** mixed */
            [
                'firstname:John|lastname!:Doe|age>:15|age<:25|postCount>=:5|postCount<=:105|countryId:(1,3,5)|cityId!:(10,12,14)',
                new ApiFilterCollection(
                    [
                        new ApiFilter('firstname', ApiFilter::OPERATION_EQUAL, 'John'),
                        new ApiFilter('lastname', ApiFilter::OPERATION_NOT_EQUAL, 'Doe'),
                        new ApiFilter('age', ApiFilter::OPERATION_MORE_THEN, 15),
                        new ApiFilter('age', ApiFilter::OPERATION_LESS_THEN, 25),
                        new ApiFilter('postCount', ApiFilter::OPERATION_MORE_OR_EQUAL_THEN, 5),
                        new ApiFilter('postCount', ApiFilter::OPERATION_LESS_OR_EQUAL_THEN, 105),
                        new ApiFilter('countryId', ApiFilter::OPERATION_IN_ARRAY, [1, 3, 5]),
                        new ApiFilter('cityId', ApiFilter::OPERATION_NOT_IN_ARRAY, [10, 12, 14]),
                    ]
                )
            ],
        ];
    }
}
