<?php

//error_reporting(E_ALL);

// doc root
define('BASE_DIR', realpath('.')."/..");

$curDir = dirname($_SERVER['PHP_SELF']);

define('BASE_URL', '/');

// Directory Defines
define('SHARED',                 BASE_DIR . '/shared/');
define('PHP_MAILER',             BASE_DIR . '/phpmailer/');
define('BOOK_COVER_IMAGES',      BASE_DIR . '/images/book_covers/');
define('MINI_BOOK_COVER_IMAGES', BASE_DIR . '/images/buttons/small_book/');

// templates
define('TEMPLATES',              BASE_DIR . '/xtpl/');
define('SETUP_TEMPLATES',        TEMPLATES . 'setup/');
define('EMAILP_TEMPLATES',       TEMPLATES . 'email/');

define('TIME', time());

define('SYSTEM_LIVE', 'live');
define('SYSTEM_TEST', 'test');
define('SYSTEM_DEV',  'dev');

// Includes
include_once(SHARED . 'config.inc.php');
include_once(SHARED . 'Utils.php');
include_once(SHARED . 'ErrorHandler.php');
include_once(SHARED . 'Request.class.php');
include_once(SHARED . 'Basket.class.php');
include_once(SHARED . 'XTemplate.class.php');
include_once(SHARED . 'Product.class.php');

session_start();

include_once(SHARED . 'tools.php');

// register_globals must be on for this app
// or we'll need $Basket = $_SESSION['Basket'] or similar

// file defines
define('INDEX_URL',        BASE_URL . 'index.php');
define('BASKET_URL',       BASE_URL . 'basket.php');
define('BOOKS_URL',        BASE_URL . 'books.php');
define('GIFTS_URL',        BASE_URL . 'books.php?command=showGiftCategories&catId=12');
define('ABOUT_US_URL',     BASE_URL . 'about_us.php');
define('MAILING_LIST_URL', BASE_URL . 'mailing.php');

// Setup URLs
define('POSTAL_SETUP_URL',       BASE_URL . 'setup/postal.php');
define('BOOK_SETUP_URL',         BASE_URL . 'setup/index.php');
define('LINKED_ITEMS_SETUP_URL', BASE_URL . 'setup/linked.php');
define('ORDER_SETUP_URL',        BASE_URL . 'setup/order.php');

// other URLS
define('IMAGE_URL', BASE_URL . 'images/');
define('CSS_URL',   BASE_URL . 'css/');

// connect to the database
connect_db();

if (!session_is_registered('Basket') || is_null($Basket)) {
    session_register('Basket');
    $Basket = new Basket();
}

?>