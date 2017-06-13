# CakePHP Chunk plugin 

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://travis-ci.org/robotusers/cakephp-chunk.svg?branch=master)](https://travis-ci.org/robotusers/cakephp-chunk)
[![codecov](https://codecov.io/gh/robotusers/cakephp-chunk/branch/master/graph/badge.svg)](https://codecov.io/gh/robotusers/cakephp-chunk)

This plugin allows to chunk results retrieved from a database in order to save memory.

## Installation

```
composer require robotusers/cakephp-chunk
bin/cake plugin load Robotusers/Chunk
```

## Using the plugin

The plugin provides a custom `ResultSet` class which accepts `Cake\ORM\Query` instance.

Example:
```php
$query = $table->find();
$results = new \Robotusers\Chunk\ORM\ResultSet($query);

foreach ($results as $result) {
    // do stuff
}
```

You can control how many elements are in one "chunk" (1000 by default):

```php
$query = $table->find();
$results = new \Robotusers\Chunk\ORM\ResultSet($query, ['size' => 100]);
```

The plugin provides also a behavior with `chunk()` method:

```php
$table->addBehavior('Robotusers/Chunk.Chunk');
$query = $table->find();
$results = $table->chunk($query, ['size' => 100]);
```
