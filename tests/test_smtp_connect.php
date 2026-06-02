<?php
$host='smtp.gmail.com';
$port=587;
$timeout=10;
$errno=0;$errstr='';
$s=@stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
var_dump(is_resource($s), $errstr, $errno);
if (is_resource($s)){
    // read server greeting
    $line = fgets($s);
    echo "GREETING: ".$line;
    fwrite($s, "EHLO php-test\r\n");
    // read a few lines
    for ($i=0;$i<3;$i++){
        $l = fgets($s);
        if ($l===false) break;
        echo "S: ".$l;
    }
    fclose($s);
}
