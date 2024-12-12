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

namespace Robotusers\Chunk\Test\TestCase\Model;

use Cake\TestSuite\TestCase;
use Robotusers\Chunk\ORM\ResultSet;
use RuntimeException;

/**
 * Description of ResultsSetTest
 *
 * @author Robert Pustułka <r.pustulka@robotusers.com>
 */
class ResultSetTest extends TestCase
{
    public array $fixtures = [
        'core.Authors',
    ];

    public function testSameResults()
    {
        $table = $this->getTableLocator()->get('Authors');

        $query = $table->find();

        $standardResults = $query->all();
        $chunkedResults = new ResultSet($query, [
            'size' => 1,
        ]);

        $this->assertEquals($standardResults->toArray(), $chunkedResults->toArray());
    }

    public function testMultipleQueriesFired()
    {
        $table = $this->getTableLocator()->get('Authors');

        $called = 0;
        $query = $table->find()->formatResults(function ($r) use (&$called) {
            $called++;

            return $r;
        });

        $results = new ResultSet($query, [
            'size' => 1,
        ]);

        $results->toList();

        $this->assertGreaterThanOrEqual($query->count(), $called);
    }

    public function testLimitAndOffset()
    {
        $table = $this->getTableLocator()->get('Authors');

        $query = $table->find()->limit(2)->page(2);

        $standardResults = $query->all();
        $chunkedResults = new ResultSet($query, [
            'size' => 1,
        ]);

        $this->assertEquals($standardResults->toArray(), $chunkedResults->toArray());
    }

    public function testSerialize()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You cannot serialize this result set.');

        $table = $this->getTableLocator()->get('Authors');
        $query = $table->find();

        $chunkedResults = new ResultSet($query);
        $chunkedResults->serialize();
    }

    public function testUnserialize()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You cannot unserialize this result set.');

        $table = $this->getTableLocator()->get('Authors');
        $query = $table->find();

        $chunkedResults = new ResultSet($query);
        $chunkedResults->unserialize('');
    }

    public function testCount()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Count is not supported yet.');

        $table = $this->getTableLocator()->get('Authors');
        $query = $table->find();

        $chunkedResults = new ResultSet($query);
        $chunkedResults->count();
    }
}
