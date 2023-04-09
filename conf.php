<?php
//conf

//zbarimg path
define('ZBARIMG', 'zbarimg');

//qrencode path
define('QRENCODE', 'qrencode');

//pngcrush
define('PNGCRUSH', 'pngcrush');

//pdfimages
define('PDFIMAGES', 'pdfimages');

//working dir
define('WORKDIR', '../work/');

//qrcode dir
define('QRCODEDIR', '../qrcodes/');

//video dir
define('VIDEODIR', '../videos/');

//upload dir
define('UPLOADDIR', '../upload/');

//matrix dir
define('MATRIX', '../matrix/');

//png dir
define('PNGDIR', '../png/');

//tcpdf
define('TCPDF', '/var/www/tcpdf/tcpdf.php');

//statefile
define('STATEFILE', 'state');

//font
define('FONT', '../font.ttf');

//blacklist img
define('BLACKLIST', '../blacklist.json');

//microsleep
define('MICROSLEEP', 100000);

//max input size
define('MAXINPUT', 737205);

//max image decode
define('MAXDECODE', 20000000);

//max qrcodes
define('MAXQRCODES', 255);

//qrcode size
define('MINSIZE', 20);
define('MAXSIZE', 190);

//maxdata per qrcodes
define('MAXDATA', 2891);

//min tmp time in sec
define('TIMEOUT', 1200);

$_SESSION['conf'] = array(
    'scripts' => array(
    'js/less.js',
        'js/jquery-2.1.1.min.js',
        'js/html5slider.js',
        'js/machin.js'
    ),
    'lang' => 'en',
    'title' => 'QRaidCODE'
);
