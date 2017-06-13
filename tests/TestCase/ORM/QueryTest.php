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

namespace Robotusers\Chunk\Test\TestCase\ORM;

use Cake\ORM\ResultSet as CoreResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Robotusers\Chunk\ORM\Query;
use Robotusers\Chunk\ORM\ResultSet;

/**
 * Description of QueryTest
 *
 * @author Robert Pustułka <r.pustulka@robotusers.com>
 */
class QueryTest extends TestCase
{
    public $fixtures = [
        'core.authors'
    ];

    public function testLoad()
    {
        $table = TableRegistry::get('Authors');
        $query = $table->find();

        $chunkedQuery = new Query($query);

        $this->assertSame($query->repository(), $chunkedQuery->repository());
        $this->assertSame($query->getConnection(), $chunkedQuery->getConnection());
        $this->assertSame($query->getOptions(), $chunkedQuery->getOptions());
    }

    public function testApplyOptions()
    {
        $table = TableRegistry::get('Authors');
        $query = $table->find();

        $chunkedQuery = new Query($query);
        $chunkedQuery->applyOptions(['chunkSize' => 100]);
        $this->assertEquals(100, $chunkedQuery->getOptions()['chunkSize']);
    }

    public function testAll()
    {
        $table = TableRegistry::get('Authors');
        $query = $table->find();

        $chunkedQuery = new Query($query);
        $chunkedQuery->applyOptions(['chunkSize' => 100]);
        $results = $chunkedQuery->all();
        $this->assertInstanceOf(ResultSet::class, $results);
        $this->assertEquals(100, $results->getConfig('size'));
    }

    public function testDefaultAll()
    {
        $table = TableRegistry::get('Authors');
        $query = $table->find();

        $chunkedQuery = new Query($query);
        $results = $chunkedQuery->defaultAll();
        $this->assertInstanceOf(CoreResultSet::class, $results);
    }
}
