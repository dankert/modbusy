# ModbusTCP Client

ModbusTCP Client for PHP

## Usage

    $client = (new ModbusTcpClient())->setHost('host.example.com')->open();

    $result = $client->readMultipleHoldingRegisters()->setUnit(1)->readFrom(40021,16);

    $client->close();

## Examples

In the [Start page](index.php) there are links to 2 real-life examples:
- Fronius Symo: Accessing the manufacturer name
- Helios KWL: Accessing the system date

## Beware

- Only reading and writing to MultipleHoldingRegisters are implemented.