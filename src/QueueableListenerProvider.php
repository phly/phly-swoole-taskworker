<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Phly\EventDispatcher\ListenerShouldQueue;
use Psr\EventDispatcher\ListenerProviderInterface;
use Swoole\Http\Server as HttpServer;

class QueueableListenerProvider implements ListenerProviderInterface
{
    /** @var ListenerProviderInterface */
    private $provider;

    /** @var HttpServer */
    private $server;

    public function __construct(HttpServer $server, ListenerProviderInterface $provider)
    {
        $this->server   = $server;
        $this->provider = $provider;
    }

    /**
     * {@inheritDocs}
     *
     * If any given listener implements ListenerShouldQueue, this method will
     * decorate the listener in a QueuableListener instance before yielding it.
     */
    public function getListenersForEvent(object $event) : iterable
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            yield $listener instanceof ListenerShouldQueue
                ? new QueueableListener($this->server, $listener)
                : $listener;
        }
    }
}
