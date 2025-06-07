<?php
ob_start();
ob_implicit_flush(0);

function CheckCanGzip(){
    global $HTTP_ACCEPT_ENCODING;
    if (headers_sent() || connection_status() >= 1){
        return 0;
    }
    if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) return "x-gzip";
    if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) return "gzip";
    return 0;
}

/* $level = compression level 0-9, 0=none, 9=max */
// level set in print_page() function

function GzDocOut($level=5,$debug=0){
    $ENCODING = CheckCanGzip();
    if ($ENCODING){
        print "\n<!-- Use compress $ENCODING -->\n";
        $Contents = ob_get_contents();
        ob_end_clean();
        if ($debug){
            $s = "<p>Not compress length: ".strlen($Contents);
            $s .= "<br />Compressed length: ".strlen(gzcompress($Contents,$level));
            $Contents .= $s;
        }
        header("Content-Encoding: $ENCODING");
        print "\x1f\x8b\x08\x00\x00\x00\x00\x00";
        $Size = strlen($Contents);
        $Crc = crc32($Contents);
        $Contents = gzcompress($Contents,$level);
        $Contents = substr($Contents, 0, strlen($Contents) - 4);
        print $Contents;
        print pack('V',$Crc);
        print pack('V',$Size);
        exit;
    }else{
        ob_end_flush();
        exit;
    }
}
?>