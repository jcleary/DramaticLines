<?php
// **************************************************************************
// BooksRequestProcessor
//
// request processor for the shopping basket
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'Basket.class.php');
include_once(SHARED . 'categories.class.php');


class BooksRequestProcessor extends RequestProcessor
{

    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()

    {

        $req = new Request();

        switch($this->Command) {

            case 'show_title':
                $Product = new Product($_REQUEST['title_id']);
                $Product->ShowDetails();
                break;

            case 'show_category':
                $Categories = new Categories();
                $Categories->ShowCategory();
                break;

            case 'showGiftCategories':
                $cat = new Categories();
                $cat->showGiftCategories();
                break;

            case 'showGiftCategory':
                $cat = new Categories();
                $cat->showGiftCategoryProducts($req->get('catId'));
                break;

            case 'show_categories':
            default:
                $Categories = new Categories();
                $Categories->ShowDetails();
                break;
        }
    }
}


?>