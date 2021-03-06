<?php

/*
 * This file is part of the Access package.
 *
 * (c) Tim <me@justim.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests;

use Access\DebugQuery;
use Access\Query;
use Tests\Fixtures\Entity\User;

class DebugQueryTest extends AbstractBaseTestCase
{
    public function testInsert(): void
    {
        // override test insert, we dont need it here
        $this->assertTrue(true);
    }

    public function testSimpleInt(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.id = ?', 1);

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.id = 1)',
            $runnableSql,
        );
    }

    public function testSimpleString(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.name = ?', 'Dave');

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.name = "Dave")',
            $runnableSql,
        );
    }

    public function testSimpleBool(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.active = ?', true);

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.active = 1)',
            $runnableSql,
        );
    }

    public function testSimpleDatetime(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.created_at < ?', new \DateTime('2000-01-01 00:00:00'));

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.created_at < "2000-01-01 00:00:00")',
            $runnableSql,
        );
    }

    public function testMultipleInts(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.id IN (?)', [1, 2]);

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.id IN (1, 2))',
            $runnableSql,
        );
    }

    public function testEscapedString(): void
    {
        $query = new Query\Select(User::class, 'u');
        $query->where('u.name = ?', 'Da"ve');

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.name = "Da\"ve")',
            $runnableSql,
        );
    }

    public function testBinaryString(): void
    {
        // see DoctrineBundle escape function tests
        $binaryString = pack('H*', '9d40b8c1417f42d099af4782ec4b20b6');

        $query = new Query\Select(User::class, 'u');
        $query->where('u.name = ?', $binaryString);

        $debug = new DebugQuery($query);

        $runnableSql = $debug->toRunnableQuery();

        $this->assertEquals(
            'SELECT `u`.* FROM `users` AS `u` WHERE (u.name = 0x9D40B8C1417F42D099AF4782EC4B20B6)',
            $runnableSql,
        );
    }
}
