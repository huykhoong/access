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

namespace Access;

use Access\Profiler;
use Access\Query\Insert;
use Access\Query\Select;
use Access\StatementPool;

/**
 * Query executer
 *
 * Keeps prepared statements open
 *
 * @author Tim <me@justim.net>
 */
final class Statement
{
    /**
     * Database connection
     *
     * @var \PDO
     */
    private \PDO $connection;

    /**
     * @var StatementPool $statementPool
     */
    private StatementPool $statementPool;

    /**
     * The query to execute
     *
     * @var Query
     */
    private Query $query;

    /**
     * The SQL to execute
     *
     * @var string|null
     */
    private ?string $sql;

    /**
     * Profiler
     *
     * @var Profiler $profiler
     */
    private Profiler $profiler;

    /**
     * Create a statement
     *
     * @param Database $db
     * @param Query $query
     */
    public function __construct(Database $db, Profiler $profiler, Query $query)
    {
        $this->connection = $db->getConnection();
        $this->statementPool = $db->getStatementPool();
        $this->query = $query;
        $this->profiler = $profiler;

        // cache the sql
        $this->sql = $query->getSql();
    }

    /**
     * Execute the query
     *
     * @return \Generator - yields Entity for select queries
     */
    public function execute(): \Generator
    {
        $profile = $this->profiler->createForQuery($this->query);

        if ($this->sql === null) {
            return $this->getReturnValue();
        }

        try {
            $profile->startPrepare();
            $statement = $this->statementPool->prepare($this->sql);
        } catch (\PDOException $e) {
            throw new Exception('Unable to prepare query', 0, $e);
        } finally {
            $profile->endPrepare();
        }

        try {
            $profile->startExecute();
            $statement->execute($this->query->getValues());
        } catch (\PDOException $e) {
            throw new Exception('Unable to execute query', 0, $e);
        } finally {
            $profile->endExecute();
        }

        if ($this->query instanceof Select) {
            try {
                $profile->startHydrate();

                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    yield $row;
                }
            } catch (\PDOException $e) {
                throw new Exception('Unable to fetch', 0, $e);
            } finally {
                $profile->endHydrate();
            }
        }

        return $this->getReturnValue();
    }

    /**
     * Get the return value based in query type
     *
     * @return ?int
     */
    private function getReturnValue(): ?int
    {
        if ($this->query instanceof Insert) {
            if ($this->sql === null) {
                // insert queries always return a string, but the type
                // of this property is string|null, so we need to check
                // for it
                // @codeCoverageIgnoreStart
                return -1;
                // @codeCoverageIgnoreEnd
            }

            return (int) $this->connection->lastInsertId();
        }

        if (!$this->query instanceof Select) {
            if ($this->sql === null) {
                return 0;
            }

            $statement = $this->statementPool->prepare($this->sql);
            return $statement->rowCount();
        }

        return null;
    }
}
