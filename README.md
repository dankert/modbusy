# ModbusTCP Client

ModbusTCP Client for PHP

## Example

    $client = (new ModbusTcpClient())->setHost('host.example.com')->open();

    $cmd = '...';
    $result = $client->readMultipleHoldingRegisters()->setAddress(11)->request( $cmd );

    $client->close();

