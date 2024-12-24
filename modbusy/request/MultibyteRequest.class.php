<?php
namespace modbusy\request;


class MultibyteRequest extends  Request
{
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

    public function request($command )
    {
        //stream_set_timeout(5);
        echo "sending ...\n";
        flush();
        $this->transactionId = rand(0,65535);
        $transaction = pack("n",$this->transactionId);

        echo "sending transaction...".bin2hex($transaction)."\n";
        flush();
        //  When sending a Modbus TCP frame, the frame is split into 6 different sections:
        //1)      Transaction Identifier ( 2 bytes )
        //2)      Protocol Identifier (2 bytes)
        //3)      Length Field (2 bytes)
        //4)      Unit Identifier (1 byte)
        //5)      Function Code (1 byte)
        //6)      Data bytes (n bytes)
        $bytes = fwrite($this->socket,$transaction);
        if   ( $bytes !== 2 )
            throw new \InvalidArgumentException("could not write transaction id to socket");

        $bytes = fwrite($this->socket,self::PROTOCOL_IDENTIFIER);
        echo "sending proto...".bin2hex(self::PROTOCOL_IDENTIFIER)."\n";
        if   ( $bytes !== 2 )
            throw new \InvalidArgumentException("could not write 00 to socket");

        // length
        $length = strlen($command)+2;
        echo "sending length...".bin2hex(pack("n",$length))."\n";
        fwrite($this->socket,pack("n",$length) ); // 2 bytes

        echo "sending unit...".bin2hex(pack("C",$this->address))."\n";
        fwrite($this->socket,pack("C",$this->address) ); // unit identifier 1 byte

        echo "sending func...".bin2hex(pack("C",$this->functionCode))."\n";
        fwrite($this->socket,pack("C",$this->functionCode) );

        echo "sending command...\n";
        $this->hex_dump($command);
        fwrite($this->socket,$command );

        echo "now getting data...";
        flush();
        $transactionResponse = fgets($this->socket,2+1);
        echo "...transaction:".bin2hex($transactionResponse)." [".strlen($transactionResponse).']';
        if   ( $transaction != $transactionResponse )
            throw new \InvalidArgumentException("transaction '".bin2hex($transactionResponse)."' does not match '".bin2hex($transaction)."'");
        flush();
        $protocol = fgets($this->socket,2+1);
        if   ( $protocol != self::PROTOCOL_IDENTIFIER )
            throw new \InvalidArgumentException("protocol does not match");
        echo "...protocol:".bin2hex($protocol);
        flush();
        $length = unpack("n",fgets($this->socket,2+1))[1];
        echo "...length:".$length."\n";
        flush();
        $response = fgets($this->socket,$length+1);


        $addressResponse = unpack("C",substr($response,0,1))[1];
        echo "...address:$addressResponse\n";
        $functionResponse = unpack("C",substr($response,1,1))[1];
        echo "...function code:".$functionResponse."\n";
        $data = substr($response,2);
        echo "...result:\n"; $this->hex_dump($data);

        if   ( $functionResponse == $this->functionCode ) {
            // success

            return $data;
        }
        elseif   ( $functionResponse == $this->functionCode + 128 ) {

            if   ( strlen($data) == 1 ) {
                $errorCode = unpack("C",$data)[1];
                if   ( isset(self::ERRORS[$errorCode]) )
                    throw new \InvalidArgumentException("server error: ".self::ERRORS[$errorCode]);

                else
                    throw new \InvalidArgumentException("server error with unknown error code ".$errorCode);
            }
            else
                throw new \InvalidArgumentException("server error with unknown error data: '".bin2hex($data));
        }
        else {
            throw new \InvalidArgumentException("server error: function in response is ".$functionResponse." and not the called function ".$function.". Data is '".bin2hex($data)."'");
        }
    }

}