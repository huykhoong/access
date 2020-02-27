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

namespace Tests\Fixtures\Repository;

use Tests\Fixtures\Entity\User;

use Access\Collection;
use Access\Query\Select;
use Access\Repository;

class UserRepository extends Repository
{
    public function findAllAsCollection(): Collection
    {
        $query = new Select(User::class);
        $query->orderBy('id ASC');

        return $this->selectCollection($query);
    }

    public function findNothing(): Collection
    {
        return $this->createEmptyCollection();
    }
}