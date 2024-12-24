<?php


$config = parse_ini_file(dirname(__FILE__)."/./config.ini" );
if   ( is_file( dirname(__FILE__)."/./config-override.ini"))
    $config = array_merge($config, parse_ini_file(dirname(__FILE__)."/./config-override.ini"));
