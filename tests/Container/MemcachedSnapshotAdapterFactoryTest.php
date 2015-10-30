<?php
/*
 * This file is part of the prooph/snapshot-memached-adapter.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/18/15 - 20:27
 */

namespace ProophTest\EventStore\Snapshot\Adapter\Memcached\Container;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\EventStore\Snapshot\Adapter\Memcached\Container\MemcachedSnapshotAdapterFactory;
use Prooph\EventStore\Snapshot\Adapter\Memcached\MemcachedSnapshotAdapter;

/**
 * Class MemcachedSnapshotAdapterFactoryTest
 * @package ProophTest\EventStore\Snapshot\Adapter\Memcached\Container
 */
final class MemcachedSnapshotAdapterFactoryTest extends TestCase
{
    /**
     * @test
     * @group my
     */
    public function it_creates_adapter_with_minimum_settings()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'prooph' => [
                'snapshot_store' => [
                    'adapter' => [
                        'type' => MemcachedSnapshotAdapter::class,
                    ]
                ]
            ]
        ]);

        $factory = new MemcachedSnapshotAdapterFactory();
        $adapter = $factory($container->reveal());

        $this->assertInstanceOf(MemcachedSnapshotAdapter::class, $adapter);
    }

    /**
     * @test
     */
    public function it_creates_adapter_with_maximum_settings()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'prooph' => [
                'snapshot_store' => [
                    'adapter' => [
                        'type' => MemcachedSnapshotAdapter::class,
                        'options' => [
                            'servers' => [
                                [
                                    'localhost',
                                    11211,
                                    0
                                ]
                            ],
                            'memcached_options' => [
                                \Memcached::SERIALIZER_PHP
                            ],
                            'persistent_id' => 'test'
                        ]
                    ]
                ]
            ]
        ]);

        $factory = new MemcachedSnapshotAdapterFactory();
        $adapter = $factory($container->reveal());

        $this->assertInstanceOf(MemcachedSnapshotAdapter::class, $adapter);
    }

    /**
     * @test
     */
    public function it_will_use_memcached_connection_alias()
    {
        $memcached = new \Memcached();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('memcached_client')->willReturn($memcached);
        $container->get('config')->willReturn([
            'prooph' => [
                'snapshot_store' => [
                    'adapter' => [
                        'type' => MemcachedSnapshotAdapter::class,
                        'options' => [
                            'memcached_connection_alias' => 'memcached_client'
                        ]
                    ]
                ]
            ]
        ]);

        $factory = new MemcachedSnapshotAdapterFactory();
        $adapter = $factory($container->reveal());

        $this->assertInstanceOf(MemcachedSnapshotAdapter::class, $adapter);
    }
}
