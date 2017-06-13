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

namespace Robotusers\Chunk\ORM;

use Cake\ORM\Query as BaseQuery;

/**
 * Description of Query
 *
 * @author Robert Pustułka <r.pustulka@robotusers.com>
 */
class Query extends BaseQuery
{

    /**
     * Constructor. Takes a query and feeds this object with its properties.
     *
     * @param BaseQuery $query Query instance.
     */
    public function __construct(BaseQuery $query)
    {
        parent::__construct($query->getConnection(), $query->repository());

        $this->load($query);
    }

    /**
     * {@inheritDoc}
     */
    public function applyOptions(array $options)
    {
        if (isset($options['chunkSize'])) {
            $this->_options['chunkSize'] = $options['chunkSize'];
        }

        return parent::applyOptions($options);
    }

    /**
     * Returns chunked reuslt set.
     *
     * @return \Robotusers\Chunk\ORM\ResultSet
     */
    public function all()
    {
        $config = [];
        if (isset($this->_options['chunkSize'])) {
            $config['size'] = $this->_options['chunkSize'];
        }

        return new ResultSet($this, $config);
    }

    /**
     * Returns default ResultSet.
     *
     * {@inheritDoc}
     */
    public function defaultAll()
    {
        return parent::all();
    }

    /**
     * Feeds this object with query properties.
     *
     * @param BaseQuery $query Query instance.
     * @return void
     */
    protected function load(BaseQuery $query)
    {
        $vars = get_object_vars($query);
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }
}
