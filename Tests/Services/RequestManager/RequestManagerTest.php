<?php

namespace Mell\SimpleDtoBundle\Tests\Services\RequestManager;

use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestManagerTest extends \PHPUnit_Framework_TestCase
{
    const BASE_PATH = 'http://example.com';

    const FIELDS_PARAM = '_fields';
    const EXPANDS_PARAM = '_expands';
    const LIMIT_PARAM = '_limit';
    const OFFSET_PARAM = '_offset';
    const SORT_PARAM = '_sort';

    /** @var RequestManagerConfigurator */
    protected $configurator;

    /**
     * @param RequestStack $requestStack
     * @param array $expected
     * @dataProvider getFieldsProvider
     */
    public function testGetFields(RequestStack $requestStack, array $expected)
    {


        $service = new RequestManager($requestStack, $this->getConfigurator());
        self::assertSameSize($expected, $service->getFields());
        self::assertEquals($expected, $service->getFields());
    }

    public function getFieldsProvider()
    {
        return [
            [$this->generateRequestStack('_fields=foo,bar'), ['foo', 'bar']],
        ];
    }

    protected function generateRequestStack(
        $fieldsStr = null,
        $expandsStr = null,
        $limitStr = null,
        $offsetStr = null,
        $sortStr = null
    ) {
        $query = implode(
            '&',
            array_filter(
                [$fieldsStr, $expandsStr, $limitStr, $offsetStr, $sortStr],
                function ($v) {return !is_null($v);}
            )
        );

        $stack = $this->createMock(RequestStack::class);
        $stack->method('getCurrentRequest')->willReturn(Request::create(self::BASE_PATH . '?' . $query));

        return $stack;
    }

    /**
     * @return RequestManagerConfigurator
     */
    protected function getConfigurator()
    {
        if (!$this->configurator) {
            $this->configurator = new RequestManagerConfigurator(
                self::FIELDS_PARAM,
                self::EXPANDS_PARAM,
                self::LIMIT_PARAM,
                self::OFFSET_PARAM,
                self::SORT_PARAM
            );
        }

        return $this->configurator;
    }
}
