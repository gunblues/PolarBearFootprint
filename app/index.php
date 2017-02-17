<?php

define('APPLICATION_PATH', dirname(__FILE__));
define('APPLICATION_INI_PATH', APPLICATION_PATH . '/conf/application.ini');

$application = new Yaf_Application(APPLICATION_INI_PATH);

$application->bootstrap()->run();
