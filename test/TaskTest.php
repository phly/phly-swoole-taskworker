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

class TaskTest extends TestCase
{
    public function setUp()
    {
        $this->result = (object) ['payload' => null];

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
        ($this->task)();

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
}
