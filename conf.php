<?php
//conf

//zbarimg path
define('ZBARIMG', '/var/www/bin/zbarimg');

//qrencode path
define('QRENCODE', '/var/www/bin/src/qrencode/qrencode');

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

$_SESSION['conf'] = array(
    'scripts' => array(
	'js/less.js',
        'js/jquery.js',
        'js/html5slider.js',
        'js/machins.js'
    ),
    'lang' => 'en',
    'title' => 'QRaidCODE',
    
)

?>