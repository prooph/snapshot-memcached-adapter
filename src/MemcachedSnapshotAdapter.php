<?php
/**
 * This file is part of the prooph/snapshot-memcached-adapter.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventStore\Snapshot\Adapter\Memcached;

use DateTimeImmutable;
use DateTimeZone;
use Memcached;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Snapshot\Adapter\Adapter;
use Prooph\EventStore\Snapshot\Snapshot;

/**
 * Class MemcachedSnapshotAdapter
 * @package Prooph\EventStore\Snapshot\Adapter\Memcached
 */
final class MemcachedSnapshotAdapter implements Adapter
{
    /**
     * @var Memcached
     */
    private $memcached;

    public function __construct(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    public function get(AggregateType $aggregateType, string $aggregateId): ?Snapshot
    {
        $key = $this->getShortAggregateTypeName($aggregateType) . '_' . $aggregateId;

        $data = $this->memcached->get($key);

        if (false === $data) {
            return null;
        }

        return new Snapshot(
            $aggregateType,
            $aggregateId,
            unserialize($data['aggregate_root']),
            $data['last_version'],
            DateTimeImmutable::createFromFormat('U.u', $data['created_at'], new DateTimeZone('UTC'))
        );
    }

    public function save(Snapshot $snapshot): void
    {
        $key = $this->getShortAggregateTypeName($snapshot->aggregateType()) . '_' . $snapshot->aggregateId();

        $data = [
            'aggregate_root' => serialize($snapshot->aggregateRoot()),
            'aggregate_type' => $snapshot->aggregateType()->toString(),
            'aggregate_id' => $snapshot->aggregateId(),
            'last_version' => $snapshot->lastVersion(),
            'created_at' => $snapshot->createdAt()->format('U.u'),
        ];

        $this->memcached->set($key, $data);
    }

    private function getShortAggregateTypeName(AggregateType $aggregateType): string
    {
        $aggregateTypeName = str_replace('-', '_', $aggregateType->toString());
        return implode('', array_slice(explode('\\', $aggregateTypeName), -1));
    }
}
