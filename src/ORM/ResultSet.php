<?php
declare(strict_types=1);

/*
 * The MIT License
 *
 * Copyright 2017 Robert Pustułka <r.pustulka@robotusers.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Robotusers\Chunk\ORM;

use Cake\Collection\CollectionTrait;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Query\SelectQuery;
use ReturnTypeWillChange;
use RuntimeException;

class ResultSet implements ResultSetInterface
{
    use CollectionTrait;
    use InstanceConfigTrait;

    /**
     * Current element.
     *
     * @var mixed
     */
    protected mixed $current;

    /**
     * Query instance.
     *
     * @var \Cake\ORM\Query\SelectQuery
     */
    protected SelectQuery $query;

    /**
     * Current chunk size.
     *
     * @var int
     */
    protected int $chunkSize = 0;

    /**
     * Current chunk index.
     *
     * @var int
     */
    protected int $chunkIndex = 0;

    /**
     * Current chunk content.
     *
     * @var array<int, mixed>
     */
    protected array $chunk;

    /**
     * Current element index.
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * Current page.
     *
     * @var int
     */
    protected int $page = 0;

    /**
     * Original query offset.
     *
     * @var ?int
     */
    protected ?int $offset;

    /**
     * Original query limit.
     *
     * @var ?int
     */
    protected ?int $limit;

    /**
     * Total count.
     *
     * @var int
     */
    protected int $count;

    /**
     * Default config.
     *
     * Currently accepts `size` option only.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'size' => 1000,
    ];

    /**
     * Constructor.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query object.
     * @param array $config Configuration.
     * @throws \RuntimeException When query is not supported.
     */
    public function __construct(SelectQuery $query, array $config = [])
    {
        $type = $query->type();
        if ($type !== 'select') {
            throw new RuntimeException(
                'You cannot chunk a non-select query.'
            );
        }

        $this->query = $query;
        $this->offset = $query->clause('offset');
        $this->limit = $query->clause('limit');

        $this->setConfig($config);
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->current;
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
        $this->chunkIndex++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
        $this->page = 1;
        $this->chunkIndex = 0;
        $this->chunkSize = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        if ($this->limit && $this->index >= $this->limit) {
            return false;
        }

        if (!$this->index) {
            $this->fetchChunk();
        }

        if ($this->chunkSize && $this->chunkIndex >= $this->chunkSize) {
            if ($this->chunkIndex < $this->getConfig('size')) {
                return false;
            }

            $this->page++;
            $this->fetchChunk();
        }

        if (!$this->chunkSize) {
            return false;
        }

        $this->current = $this->chunk[$this->chunkIndex];

        return true;
    }

    /**
     * Updates the current chunk.
     *
     * @return void
     */
    protected function fetchChunk(): void
    {
        $size = $this->getConfig('size');

        if ($this->limit) {
            $left = $this->limit - $this->index;
            $limit = $size < $left ? $size : $left;
        } else {
            $limit = $size;
        }

        $offset = ($this->page - 1) * $size;

        $query = clone $this->query;
        $query->enableBufferedResults()
            ->offset($this->offset + $offset)
            ->limit($limit);

        $this->chunk = $query->all()->toList();
        $this->chunkSize = count($this->chunk);
        $this->chunkIndex = 0;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        throw new RuntimeException('Count is not supported yet.');
    }

    /**
     * {@inheritDoc}
     *
     * Serialization is not supported (yet).     *
     */
    public function serialize(): never
    {
        throw new RuntimeException('You cannot serialize this result set.');
    }

    public function __serialize(): never
    {
        throw new RuntimeException('You cannot serialize this result set.');
    }

    /**
     * {@inheritDoc}
     *
     * Serialization is not supported (yet).     *
     */
    public function unserialize(string $serialized): never
    {
        throw new RuntimeException('You cannot unserialize this result set.');
    }

    public function __unserialize(array $data): never
    {
        throw new RuntimeException('You cannot unserialize this result set.');
    }
}
