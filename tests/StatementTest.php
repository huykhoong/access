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

use Access\Exception;
use Access\Query;
use Tests\AbstractBaseTestCase;
use Tests\Fixtures\Entity\User;

class StatementTest extends AbstractBaseTestCase
{
    public function testInsert(): void
    {
        // override test insert, we dont need it here
        $this->assertTrue(true);
    }

    public function testInvalidPrepare(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to prepare query');

        $query = new Query\Raw('SELECT foo FRM bar');
        self::$db->query($query);
    }

    public function testInvalidExectute(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to execute query');

        $query = new Query\Insert(User::class);
        $query->values([
            'id' => 1,
            'name' => 'Dave',
            'email' => 'dave@example.com',
        ]);

        self::$db->query($query);

        // insert with same primary key value
        self::$db->query($query);
    }

    public function testEmptyReturnValue(): void
    {
        $user = new User();
        $user->setName('Dave');
        $user->setEmail('dave@example.com');

        self::$db->save($user);

        $this->assertEquals(2, $user->getId());
        self::$db->save($user);
    }
}
