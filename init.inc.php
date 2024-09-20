<?php

define('MINI_ENGINE_LIBRARY', true);
define('MINI_ENGINE_ROOT', __DIR__);
require_once(__DIR__ . '/mini-engine.php');
if (file_exists(__DIR__ . '/config.inc.php')) {
    include(__DIR__ . '/config.inc.php');
} elseif (file_exists("/srv/config/our-minutes.inc.php")) {
    include("/srv/config/our-minutes.inc.php");
}
set_include_path(
    __DIR__ . '/libraries'
    . PATH_SEPARATOR . __DIR__ . '/models'
);
MiniEngine::initEnv();
