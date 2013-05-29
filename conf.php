<?php
//conf

//zbarimg path
define('ZBARIMG', '/var/www/bin/zbarimg');

//qrencode path
define('QRENCODE', '/var/www/bin/src/qrencode/qrencode');

//pngcrush
define('PNGCRUSH', '/var/www/bin/src/pngcrush/pngcrush');

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

//tfpdf
define('TFPDF', '/var/www/tfpdf/tfpdf.php');

//statefile
define('STATEFILE', 'state');

//font
define('FONT', '../font.ttf');


//max input size
define('MAXINPUT', 300000);

//max qrcodes
define('MAXQRCODES', 256);

//qrcode size
define('MINSIZE', 40);
define('MAXSIZE', 190);

//maxdata per qrcodes
define('MAXDATA', 2891);


$_SESSION['conf'] = array(
    'scripts' => array(
	'js/less.js',
        'js/jquery.js',
        'js/html5slider.js',
        'js/machin.js'
    ),
    'lang' => 'en',
    'title' => 'QRaidCODE',
    
)

?>