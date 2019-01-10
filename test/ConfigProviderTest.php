<?php
/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/phly/phly-swoole-taskworker/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use PHPUnit\Framework\TestCase;
use Phly\Swoole\TaskWorker\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
    }
}
