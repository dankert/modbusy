<html lang="en">
<body>
<?php

use modbusy\ModbusTcpClient;

header("Content-Type: text/html");
echo "modbus\n";
require('./autoload.php');
$config=[];
require('./config/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$client = (new ModbusTcpClient())->setHost($config['host'])->setLog( function ($log){ echo($log."\n");} )->open();


function readHeliosKWLVariable( $client,$variable,$valueLength )
{
    echo "<pre>";
    // Cite from the documentation:
    //
    // Das Lesen von Variablen erfordert ein zweistufiges Vorgehen:
    // 1. Zuerst muss die Bezeichnung der zu lesenden Variable im Format „vXXXXX\0“ als HEX-Zeichen codiert in die
    // Modbus-Register ab Registeradresse 1 geschrieben werden.
    // 2. Mit der anschließenden Abfrage kann die angeforderte Variable ab der Registeradresse 1 gelesen werden. Die an-
    // geforderte Variable wird dann im Format „vXXXXX=YYYYY...\0“ (als HEX-Zeichen codiert) zurückgegeben.


    $var = $variable."\x00\x00"; // Variable name + NUL
    $startReference = "\x00\x01";
    echo "result1 ready\n";
    $client->writeMultipleHoldingRegisters()->setAddress(180)->request(
            $startReference .pack("n",ceil(strlen($var)/2)).pack("C",strlen($var)).$var
        );
    echo "\n";
    // 8 bytes from startadress 1
    $result = $client->readMultipleHoldingRegisters()->setAddress(180)->request(
            $startReference . pack("n",ceil((9+$valueLength)/2) )
        );
    $length = unpack("C",substr($result,0,1))[1];
    echo "Laenge: $length\n";
    $value = substr(rtrim($result,"\x00"),1);
    echo "</pre>";
    echo "Value of '$variable': $value\n";
}


// get outdoor temperature from helios kwl modbus tcp
readHeliosKWLVariable( $client,"v00104",7);

// get system date from helios kwl modbus tcp
readHeliosKWLVariable($client,"v00004",9);

$client->close();

?></body></html>