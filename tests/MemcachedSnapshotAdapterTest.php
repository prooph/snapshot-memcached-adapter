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

namespace ProophTest\EventStore\Snapshot\Adpater\Memcached;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Snapshot\Adapter\Memcached\MemcachedSnapshotAdapter;
use Prooph\EventStore\Snapshot\Snapshot;

/**
 * Class MemcachedSnapshotAdapterTest
 * @package ProophTest\EventStore\Adpater\Memcached
 */
final class MemcachedSnapshotAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function it_saves_and_reads(): void
    {
        $m = new \Memcached();
        $m->addServer('localhost', 11211);

        $adapter = new MemcachedSnapshotAdapter($m);

        $aggregateType = AggregateType::fromString('foo');

        $aggregateRoot = new \stdClass();
        $aggregateRoot->foo = 'bar';

        $time = (string) microtime(true);
        if (false === strpos($time, '.')) {
            $time .= '.0000';
        }
        $now = \DateTimeImmutable::createFromFormat('U.u', $time, new \DateTimeZone('UTC'));

        $snapshot = new Snapshot($aggregateType, 'id', $aggregateRoot, 1, $now);

        $adapter->save($snapshot);

        $this->assertNull($adapter->get($aggregateType, 'invalid'));

        $readSnapshot = $adapter->get($aggregateType, 'id');

        $this->assertEquals($snapshot, $readSnapshot);
    }
}
