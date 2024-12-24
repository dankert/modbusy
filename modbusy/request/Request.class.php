<?php
namespace modbusy\request;


use Closure;

class Request
{
    /**
     * @var integer
     */
    protected $functionCode;
    /**
     * @var mixed
     */
    protected $socket;
    /**
     * @var Closure
     */
    protected $log;

    public function __construct()
    {

    }

    public function setClient( $client )
    {
        $this->client = $client;
    }
    public function setFunctionCode( $fc )
    {
        $this->functionCode = $fc;
    }
    public function setSocket( $socket )
    {
        $this->socket = $socket;
    }

    public function setTransactionId( $transactionId )
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function setAddress($address )
    {
        $this->address = $address;
        return $this;
    }

    public $transactionId = 1024; // optional
    public $address = 1;

    const PROTOCOL_IDENTIFIER = "\x00\x00";

    const FUNCTION_Read_Multiple_Holding_Registers = 3;
    const ERRORS = [
1 =>	'Illegal Function 	Function code received in the query is not recognized or allowed by server',
2 =>	'Illegal Data Address 	Data address of some or all the required entities are not allowed or do not exist in server',
3 =>	'Illegal Data Value 	Value is not accepted by server',
4 =>	'Server Device Failure 	Unrecoverable error occurred while server was attempting to perform requested action',
5 =>	'Acknowledge 	Server has accepted request and is processing it, but a long duration of time is required. This response is returned to prevent a timeout error from occurring in the client. client can next issue a Poll Program Complete message to determine whether processing is completed',
6 =>	'Server Device Busy 	Server is engaged in processing a long-duration command; client should retry later',
7 =>	'Negative Acknowledge 	Server cannot perform the programming functions; client should request diagnostic or error information from server',
8 =>	'Memory Parity Error 	Server detected a parity error in memory; client can retry the request',
10=> 	'Gateway Path Unavailable 	Specialized for Modbus gateways: indicates a misconfigured gateway',
11=> 	'Gateway Target Device Failed to Respond',
    ];


    function strToHex($string){
        $hex=[];
        for ($i=0; $i < strlen($string); $i++){
            $hex[] = strtoupper(str_pad(dechex(ord($string[$i])),2,'0',STR_PAD_LEFT));
        }
        return implode(' ',$hex);
    }


    function hex_dump($data, $newline="\n")
    {
        static $from = '';
        static $to = '';

        static $width = 16; # number of bytes per line

        static $pad = '.'; # padding for non-visible characters

        if ($from==='')
        {
            for ($i=0; $i<=0xFF; $i++)
            {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width*2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line)
        {
            echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }

    public function setLog(Closure $log)
    {
        $this->log = $log;
    }


    protected function logHex()
    {

    }

    protected function logHexDump()
    {

    }
}