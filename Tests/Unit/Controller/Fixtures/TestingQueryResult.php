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
        $key = $this->objectStorage->key();

        return \is_string($key) ? $key : '';
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
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Not implemented.', 1714832632);
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Not implemented.', 1714832637);
    }

    public function count(): int
    {
        return $this->objectStorage->count();
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function getQuery(): QueryInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    /**
     * @param QueryInterface<FrontendUserGroup> $query
     *
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function setQuery(QueryInterface $query): void
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
