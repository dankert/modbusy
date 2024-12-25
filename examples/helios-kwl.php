<html lang="en">
<body>
<?php

use modbusy\ModbusTcpClient;

header("Content-Type: text/html");
echo "modbus\n";
require('../autoload.php');
require('../config/config.php');
$config = readConfig('helios-kwl');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$client = (new ModbusTcpClient())->setHost($config['host'])->setLog( function ($log){ echo('<pre>'.$log."</pre>\n");} )->open();

/**
 * Reading a value from the device.
 *
 * @param $client ModbusTcpClient
 * @param $variable
 * @param $valueLength
 * @return void
 */
function readEasyControlsVariable($client, $variable, $valueLength )
{
    // THIS IS A RELLY BAD BAD DESIGN BY HELIOS:
    // You have to write the variable name into the same adress for all requests.
    // And than you have to read the value from the same adress.
    // This has nothing to do which the modbus tcp spec!
    // BE CAREFUL OF RACE CONDITIONS:
    // If another client is writing the variable name into the register at the same time, then you maybe getting a response for another variable in the 2nd request (head->table).
    // It is better to shut down your smarthome if it is requesting values via modbus tcp the same time.
    // I'll repeat: This is a VERY POOR AND BAD DESIGN BY HELIOS!
    // And that has nothing to do with modbus tcp, which is itself a robust mechanism.

    // Cite from the documentation:
    //
    // Das Lesen von Variablen erfordert ein zweistufiges Vorgehen:
    // 1. Zuerst muss die Bezeichnung der zu lesenden Variable im Format „vXXXXX\0“ als HEX-Zeichen codiert in die
    // Modbus-Register ab Registeradresse 1 geschrieben werden.
    // 2. Mit der anschließenden Abfrage kann die angeforderte Variable ab der Registeradresse 1 gelesen werden. Die an-
    // geforderte Variable wird dann im Format „vXXXXX=YYYYY...\0“ (als HEX-Zeichen codiert) zurückgegeben.


    $var = $variable."\x00\x00"; // Variable name + NUL
    $startReference = 1; // Immer Startadresse 1.
    echo "result ready\n";
    $client->writeMultipleHoldingRegisters()->setUnit(180)->write($startReference,$var);
    echo "\n";
    // 8 bytes from startadress 1
    $result = $client->readMultipleHoldingRegisters()->setUnit(180)->readFrom($startReference,$valueLength);
    $length = unpack("C",substr($result,0,1))[1];
    echo "Laenge: $length\n";
    $value = substr(rtrim($result,"\x00"),1);
    echo "Value of '$variable': $value\n";
}


// get outdoor temperature from helios kwl modbus tcp
readEasyControlsVariable( $client,"v00104",7);

// get system date from helios kwl modbus tcp
readEasyControlsVariable($client,"v00004",9);

$client->close();

?></body></html>