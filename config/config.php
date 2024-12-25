<?php

function readConfig( $prefix) {

    $configName = 'config-'.$prefix;

    $config = parse_ini_file(dirname(__FILE__)."/./".$configName.".ini" );
    if   ( is_file( dirname(__FILE__)."/./".$configName."-override.ini"))
        $config = array_merge($config, parse_ini_file(dirname(__FILE__)."/./".$configName."-override.ini"));

    return $config;
}
