<?php

namespace Mell\Bundle\SimpleDtoBundle\Tests\Services\RequestManager;

use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestManagerTest extends \PHPUnit_Framework_TestCase
{
    const PARAM_FIELDS = '_fields';
    const PARAM_EXPANDS = '_expands';
    const PARAM_LIMIT = '_limit';
    const PARAM_OFFSET = '_offset';
    const PARAM_SORT = '_sort';

    /**
     * @param RequestStack $stack
     * @param array $expected
     * @dataProvider getFieldsProvider
     * @group fields
     */
    public function testGetFields(RequestStack $stack, array $expected)
    {
        $manager = new RequestManager($stack, $this->getConfigurator());
        $this->assertEquals($expected, $manager->getFields());
    }

    /**
     * @param RequestStack $stack
     * @param array $expected
     * @dataProvider getExpandsProvider
     * @group expands
     */
    public function testGetExpands(RequestStack $stack, array $expected)
    {
        $manager = new RequestManager($stack, $this->getConfigurator());
        $this->assertEquals($expected, $manager->getExpands());
    }

    /**
     * @param RequestStack $stack
     * @param $expected
     * @dataProvider getLimitProvider
     * @group limit
     */
    public function testGetLimit(RequestStack $stack, $expected)
    {
        $manager = new RequestManager($stack, $this->getConfigurator());
        $this->assertEquals($expected, $manager->getLimit());
    }

    /**
     * @return array
     */
    public function getFieldsProvider()
    {
        return [
            [$this->createStack(), []],
            [$this->createStack('foo'), ['foo']],
            [$this->createStack('foo,bar'), ['foo', 'bar']], // _fields=foo,bar
            [$this->createStack('foo,bar,foo'), ['foo', 'bar']],
            [$this->createStack('foo,bar,baz'), ['foo', 'bar', 'baz']],
            [$this->createStack('foo,foo,foo'), ['foo']],
            [$this->createStack('foo, bar'), ['foo', 'bar']],
            [$this->createStack('foo, bar,    baz'), ['foo', 'bar', 'baz']],
        ];
    }

    /**
     * @return array
     */
    public function getExpandsProvider()
    {
        return [
            [$this->createStack(), []],
            [$this->createStack(null, 'foo'), ['foo']], // _expands=foo
            [$this->createStack(null, 'foo,bar'), ['foo', 'bar']],
            [$this->createStack(null, 'foo,bar,foo'), ['foo', 'bar']],
            [$this->createStack(null, 'foo,bar,baz'), ['foo', 'bar', 'baz']],
            [$this->createStack(null, 'foo,foo,foo'), ['foo']],
            [$this->createStack(null, 'foo, bar'), ['foo', 'bar']],
            [$this->createStack(null, 'foo, bar,    baz'), ['foo', 'bar', 'baz']],
        ];
    }

    /**
     * @return array
     */
    public function getLimitProvider()
    {
        return [
            [$this->createStack(), 0],
        ];
    }

    /**
     * @return RequestManagerConfigurator
     */
    private function getConfigurator()
    {
        return new RequestManagerConfigurator(
            self::PARAM_FIELDS,
            self::PARAM_EXPANDS,
            self::PARAM_LIMIT,
            self::PARAM_OFFSET,
            self::PARAM_SORT
        );
    }

    /**
     * @param string|null $fieldsStr
     * @param string|null $expandsStr
     * @param string|null $limitStr
     * @param string|null $offsetStr
     * @param string|null $sortStr
     * @return RequestStack
     */
    private function createStack(
        $fieldsStr = null,
        $expandsStr = null,
        $limitStr = null,
        $offsetStr = null,
        $sortStr = null
    ) {
        $query = [
            self::PARAM_FIELDS => $fieldsStr,
            self::PARAM_EXPANDS => $expandsStr,
            self::PARAM_LIMIT => $limitStr,
            self::PARAM_OFFSET => $offsetStr,
            self::PARAM_SORT => $sortStr,
        ];
        $request = new Request(array_filter($query, function ($v) { return !empty($v); }));


        $stack = $this->createMock(RequestStack::class);
        $stack->method('getCurrentRequest')->willReturn($request);

        return $stack;
    }
}
