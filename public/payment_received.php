<?php
include_once('application.php');
include_once(SHARED . 'StaticPages.class.php');

$sp = new StaticPages();
$sp->showPaymentReceivedPage();

?>