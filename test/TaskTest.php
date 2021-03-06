<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Closure;
use Phly\Swoole\TaskWorker\Task;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TaskTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
        $this->result    = (object) ['payload' => null];

        $this->handler = function ($a, $b) {
            $this->result->payload = [
                'a' => $a,
                'b' => $b,
            ];
        };

        $this->task = new Task($this->handler, 'first', 'second');
    }

    public function testTaskIsInvokable()
    {
        ($this->task)($this->container);

        $this->assertSame([
            'a' => 'first',
            'b' => 'second',
        ], $this->result->payload);
    }

    public function testTaskIsSerializable()
    {
        $expected = [
            'handler'   => Closure::class,
            'arguments' => [
                'first',
                'second',
            ],
        ];

        $this->assertSame($expected, $this->task->jsonSerialize());
    }

    public function testTaskCanSerializeStringHandlerName()
    {
        $task = new Task('str_replace', 'foo', 'bar', 'foobar');
        $expected = [
            'handler'   => 'str_replace',
            'arguments' => [
                'foo',
                'bar',
                'foobar',
            ],
        ];

        $this->assertSame($expected, $task->jsonSerialize());
    }

    public function testTaskCanSerializeStaticMethodNotation()
    {
        $task = new Task(TestCase::class . '::fail');
        $expected = [
            'handler'   => TestCase::class . '::fail',
            'arguments' => [],
        ];

        $this->assertSame($expected, $task->jsonSerialize());
    }

    public function testTaskCanSerializeInstanceMethod()
    {
        $task = new Task([$this, 'setUp']);
        $expected = [
            'handler'   => __CLASS__ . '::setUp',
            'arguments' => [],
        ];

        $this->assertSame($expected, $task->jsonSerialize());
    }

    public function testTaskCanSerializeStaticMethodViaArrayNotation()
    {
        $task = new Task([__CLASS__, 'fail']);
        $expected = [
            'handler'   => __CLASS__ . '::fail',
            'arguments' => [],
        ];

        $this->assertSame($expected, $task->jsonSerialize());
    }
}
