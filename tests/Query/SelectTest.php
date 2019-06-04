<?php

declare(strict_types=1);

namespace Tests\Query;

use PHPUnit\Framework\TestCase;

use Access\Query\Select;
use Tests\Fixtures\Entity\User;
use Tests\Fixtures\Entity\Project;

class SelectTest extends TestCase
{
    public function testQuery(): void
    {
        $query = new Select(User::class);

        $this->assertEquals('SELECT `users`.* FROM `users`', $query->getSql());
    }

    public function testQueryWithAlias(): void
    {
        $query = new Select(User::class, 'u');

        $this->assertEquals('SELECT `u`.* FROM `users` AS `u`', $query->getSql());
    }

    public function testQueryWhere(): void
    {
        $name = 'John';

        $query = new Select(User::class);
        $query->where([
            'name = ?' => $name,
        ]);

        $this->assertEquals('SELECT `users`.* FROM `users` WHERE (name = :w0)', $query->getSql());
        $this->assertEquals(['w0' => $name], $query->getValues());

        $query = new Select(User::class);
        $query->where('name IS NOT NULL');

        $this->assertEquals('SELECT `users`.* FROM `users` WHERE (name IS NOT NULL)', $query->getSql());
        $this->assertEquals([], $query->getValues());

        $query = new Select(User::class);
        $query->where('name = ?', $name);

        $this->assertEquals('SELECT `users`.* FROM `users` WHERE (name = :w0)', $query->getSql());
        $this->assertEquals(['w0' => $name], $query->getValues());
    }

    public function testQueryJoin(): void
    {
        $query = new Select(Project::class, 'p');
        $query->innerJoin(User::class, 'u', 'p.owner_id = u.id');

        $this->assertEquals(
            'SELECT `p`.* FROM `projects` AS `p` INNER JOIN `users` AS `u` ON (p.owner_id = u.id)',
            $query->getSql()
        );

        $query = new Select(Project::class, 'p');
        $query->leftJoin(User::class, 'u', 'p.owner_id = u.id');

        $this->assertEquals(
            'SELECT `p`.* FROM `projects` AS `p` LEFT JOIN `users` AS `u` ON (p.owner_id = u.id)',
            $query->getSql()
        );
    }

    public function testQueryGroupBy(): void
    {
        $query = new Select(Project::class, 'p');
        $query->groupBy('p.id');

        $this->assertEquals('SELECT `p`.* FROM `projects` AS `p` GROUP BY p.id', $query->getSql());
    }

    public function testQueryLimit(): void
    {
        $query = new Select(Project::class, 'p');
        $query->limit(10);

        $this->assertEquals('SELECT `p`.* FROM `projects` AS `p` LIMIT 10', $query->getSql());
    }

    public function testQueryHaving(): void
    {
        $query = new Select(User::class, 'u', [
            'total_projects' => 'COUNT(p.id)',
        ]);
        $query->leftJoin(Project::class, 'p', 'p.owner_id = u.id');

        $query->groupBy('u.id');
        $query->having([
            'total_projects > ?' => 1,
        ]);

        $query->having('u.name IS NOT NULL');

        $this->assertEquals(
            'SELECT `u`.*, COUNT(p.id) AS total_projects FROM `users` AS `u` LEFT JOIN `projects` AS `p` ON '
            . '(p.owner_id = u.id) GROUP BY u.id HAVING (total_projects > :h0) AND (u.name IS NOT NULL)',
            $query->getSql()
        );

        $this->assertEquals(['h0' => 1], $query->getValues());
    }

    public function testQueryOrder(): void
    {
        $query = new Select(Project::class, 'p');
        $query->orderBy('p.name ASC');

        $this->assertEquals('SELECT `p`.* FROM `projects` AS `p` ORDER BY p.name ASC', $query->getSql());
    }
}
