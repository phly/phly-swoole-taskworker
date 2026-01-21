<?php

/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 */

declare(strict_types=1);

namespace Phlytest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\DeferredListenerDelegator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use stdClass;
use Swoole\Http\Server as HttpServer;

class DeferredListenerDelegatorTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->server    = $this->createMock(HttpServer::class);
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testReturnsOriginalServiceIfNotCallable()
    {
        $instance = new stdClass();
        $factory  = function () use ($instance) {
            return $instance;
        };

        $delegator = new DeferredListenerDelegator();

        $this->assertSame($instance, $delegator(
            $this->container->reveal(),
            'listener',
            $factory
        ));
        $this->container->get(HttpServer::class)->shouldNotHaveBeenCalled();
    }

    public function testReturnsDecoratedListenerWhenOriginalServiceIsCallable()
    {
        $listener = function ($event) {
        };
        $factory  = function () use ($listener) {
            return $listener;
        };
        $this->container->get(HttpServer::class)->willReturn($this->server);

        $delegator = new DeferredListenerDelegator();

        $instance = $delegator(
            $this->container->reveal(),
            'listener',
            $factory
        );

        $this->assertNotSame($listener, $instance);
        $this->container->get(HttpServer::class)->shouldHaveBeenCalled();
    }
}
