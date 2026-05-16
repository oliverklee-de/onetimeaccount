<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Testing query result that holds an object storage for its objects.
 *
 * @implements QueryResultInterface<FrontendUserGroup>
 */
final class TestingQueryResult implements QueryResultInterface
{
    /**
     * @param ObjectStorage<FrontendUserGroup> $objectStorage
     */
    public function __construct(private readonly ObjectStorage $objectStorage) {}

    public function current(): FrontendUserGroup
    {
        return $this->objectStorage->current();
    }

    public function next(): void
    {
        $this->objectStorage->next();
    }

    public function key(): string
    {
        return $this->objectStorage->key();
    }

    public function valid(): bool
    {
        return $this->objectStorage->valid();
    }

    public function rewind(): void
    {
        $this->objectStorage->rewind();
    }

    public function offsetExists($offset): bool
    {
        return $this->objectStorage->offsetExists((int)$offset);
    }

    public function offsetGet($offset): ?FrontendUserGroup
    {
        $offset = $this->objectStorage->offsetGet((int)$offset);

        return $offset instanceof FrontendUserGroup ? $offset : null;
    }

    /**
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value): never
    {
        throw new \BadMethodCallException('Not implemented.', 1714832632);
    }

    /**
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset): never
    {
        throw new \BadMethodCallException('Not implemented.', 1714832637);
    }

    public function count(): int
    {
        return $this->objectStorage->count();
    }

    /**
     * @throws \BadMethodCallException
     */
    public function getQuery(): never
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    /**
     * @param QueryInterface<FrontendUserGroup> $query
     *
     * @throws \BadMethodCallException
     */
    public function setQuery(QueryInterface $query): never
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    public function getFirst(): FrontendUserGroup
    {
        $this->objectStorage->rewind();
        return $this->objectStorage->current();
    }

    /**
     * @return list<FrontendUserGroup>
     */
    public function toArray(): array
    {
        return $this->objectStorage->toArray();
    }
}
