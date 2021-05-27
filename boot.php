<?php
/**
 * OpenTHC Chat Bootstrap
 */

error_reporting(E_ALL & ~E_NOTICE);

define('APP_ROOT', __DIR__);
define('APP_SALT', 'c7527f87015dbb20a8348da3f1fccaba7423884f13cb6acb58e7c23e44e6f995');

require_once(__DIR__ . '/vendor/autoload.php');

\OpenTHC\Config::init(__DIR__);
