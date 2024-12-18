<?php
declare(strict_types=1);

/**
 * Test suite bootstrap.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */

use Cake\Core\BasePlugin;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;

error_reporting(E_ALL & ~E_USER_DEPRECATED);

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);

define('PLUGIN_ROOT', $root);
chdir($root);

if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
}

ConnectionManager::setConfig('test', ['url' => 'sqlite://tmp/test.sqlite']);

$loader = new SchemaLoader();
$loader->loadInternalFile($root . '/tests/schema.php');

Plugin::getCollection()->add(new BasePlugin([
    'name' => 'Robotusers/Chunk',
    'path' => PLUGIN_ROOT . DS,
]));
