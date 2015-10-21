<?php
/*
 * This file is part of the prooph/snapshot-memcached-adapter.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/21/15 - 20:18
 */

namespace Prooph\EventStore\Snapshot\Adapter\Memcached\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Prooph\EventStore\Snapshot\Adapter\Memcached\MemcachedSnapshotAdapter;

/**
 * Class MemcachedSnapshotAdapterFactory
 * @package Prooph\EventStore\Snapshot\Adapter\Memcached\Container
 */
final class MemcachedSnapshotAdapterFactory implements RequiresConfig, RequiresMandatoryOptions, ProvidesDefaultOptions
{
    use ConfigurationTrait;

    /**
     * @param ContainerInterface $container
     * @return MemcachedSnapshotAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $this->options($config)['snapshot_adapter']['options'];

        if (isset($config['memcached_connection_alias'])) {
            $memcached = $container->get($config['memcached_connection_alias']);
        } else {
            $memcached = new \Memcached($config['persistent_id']);
            $memcached->addServers($config['servers']);
            $memcached->setOptions($config['memcached_options']);
        }

        return new MemcachedSnapshotAdapter($memcached);
    }

    /**
     * @inheritdoc
     */
    public function vendorName()
    {
        return 'prooph';
    }

    /**
     * @inheritdoc
     */
    public function packageName()
    {
        return 'event_store';
    }

    /**
     * @inheritdoc
     */
    public function mandatoryOptions()
    {
        return ['snapshot_adapter'];
    }

    /**
     * @inheritdoc
     */
    public function defaultOptions()
    {
        return [
            'snapshot_adapter' => [
                'options' => [
                    'persistent_id' => null,
                    'servers' => [
                        [
                            'localhost',
                            11211,
                            0
                        ]
                    ],
                    'memcached_options' => [],
                ]
            ]
        ];
    }
}
