<html lang="en">
<body>
<?php

use modbusy\ModbusTcpClient;

header("Content-Type: text/html");
require('../autoload.php');

require('../config/config.php');
$config = readConfig('fronius-symo');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$inverterDeviceId = 1; // number of inverter

$client = (new ModbusTcpClient())->setHost($config['host'])->setLog( function ($log){ echo('<pre>'.$log."</pre>");} )->open();

// Manufacturer
// Address 40004 (corresponds to register 40005) length 8 registers (=16 bytes)
$response = $client->readMultipleHoldingRegisters()->setUnit($inverterDeviceId)->readFrom(40005-1,8);

$length = unpack("C",substr($response,0,1))[1];
echo "Laenge: $length\n";
$value = substr(rtrim($response,"\x00"),1);
echo "Value: ".htmlentities($value)."<br/>";

// device id
$response = $client->readMultipleHoldingRegisters()->setUnit($inverterDeviceId)->readFrom(40021-1,16);

$length = unpack("C",substr($response,0,1))[1];
echo "Laenge: $length\n";
$value = substr(rtrim($response,"\x00"),1);
echo "Value: ".htmlentities($value)."<br/>";



$client->close();

?></body></html>