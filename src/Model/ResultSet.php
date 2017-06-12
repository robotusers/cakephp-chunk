<?php
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
namespace Robotusers\Chunk\Model;

use Cake\Collection\CollectionTrait;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Query;
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
    protected $current;

    /**
     * Query instance.
     *
     * @var Query
     */
    protected $query;

    /**
     * Current chunk size.
     *
     * @var int
     */
    protected $chunkSize = 0;

    /**
     * Current chunk index.
     *
     * @var int
     */
    protected $chunkIndex = 0;

    /**
     * Current chunk content.
     *
     * @var array
     */
    protected $chunk;

    /**
     * Current element index.
     *
     * @var int
     */
    protected $index = 0;

    /**
     * Current page.
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Original query offset.
     *
     * @var int
     */
    protected $offset;

    /**
     * Original query limit.
     *
     * @var int
     */
    protected $limit;

    /**
     * Total count.
     *
     * @var int
     */
    protected $count;

    /**
     * Default config.
     *
     * Currently accepts `size` option only.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'size' => 1000
    ];

    /**
     * Constructor.
     *
     * @param Query $query Query object.
     * @param array $config Configuration.
     * @throws RuntimeException When query is not supported.
     */
    public function __construct(Query $query, array $config = [])
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
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->index++;
        $this->chunkIndex++;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->index = 0;
        $this->chunkIndex = 0;
        $this->page = 1;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        if ($this->limit && $this->index >= $this->limit) {
            return false;
        }

        if (!$this->index) {
            $this->fetchChunk();
        }

        if ($this->chunkSize && $this->chunkIndex >= $this->chunkSize) {
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
    protected function fetchChunk()
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
        $query->bufferResults(true)
            ->offset($this->offset + $offset)
            ->limit($limit);

        $this->chunk = $query->all()->toList();
        $this->chunkSize = count($this->chunk);
        $this->chunkIndex = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = $this->query->count();
        }

        return $this->count;
    }

    /**
     * Serialization is not supported (yet).
     *
     * {@inheritDoc}
     */
    public function serialize()
    {
        throw new RuntimeException('You cannot serialize this result set.');
    }

    /**
     * Serialization is not supported (yet).
     *
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        throw new RuntimeException('You cannot unserialize this result set.');
    }
}
