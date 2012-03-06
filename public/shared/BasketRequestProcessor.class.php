<?php
// **************************************************************************
// BasketRequestProcessor
//
// request processor for the shopping basket
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'Basket.class.php');
include_once(SHARED . 'categories.class.php');
include_once(SHARED . 'Request.class.php');

class BasketRequestProcessor extends RequestProcessor
{

    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {
        $Basket = $_SESSION['Basket'];
        $displayDone = false;
        $request = new Request();

        switch($this->Command) {
            case 'add_item':
                $addItemId = $request->get('title_id');
                $Product = new Product($addItemId);
                $Basket->AddItem($Product);

                if ($request->get('catId')) {

                    $cat = new Categories();
                    $cat->showGiftCategoryProducts($request->get('catId'));

                } else {

                    if ($returnId = $request->get('return_id')) {
                        $Product = new Product($returnId);
                    }

                    $Product->ShowDetails();

                }

                $displayDone = true;
            break;

            case 'update_quantities':
                $Basket->UpdateQuantities();
            break;

            case 'submit_basket':
                $Basket->UpdateQuantities();
                if (!$Basket->shippingSaved) {
                    $Basket->ShowShippingInfo();
                } elseif (!$Basket->paymentSaved) {
                    $Basket->showPaymentOptions();
                } else {
                    $Basket->showBasket(BASKET_CONFIRM);
                }
                $displayDone = true;
            break;

            case 'shipping_info':
                $Basket->UpdateQuantities();
                $Basket->ShowShippingInfo();
                $displayDone = true;
            break;

            case 'submit_shipping_info':
                $Basket->saveShippingInfo();
                $Basket->validateShippingInfo();
                if ($Basket->hasErrors()) {
                    $Basket->ShowShippingInfo();
                } elseif (!$Basket->paymentSaved) {
                    $Basket->showPaymentOptions();
                } else {
                    $Basket->showBasket(BASKET_CONFIRM);
                }
                $displayDone = true;
            break;

            case 'payment_method':
                $Basket->showPaymentOptions();
                $displayDone = true;
            break;

            case 'submit_payment_method':
                $Basket->savePaymentMethod();
                $Basket->showBasket(BASKET_CONFIRM);
                $displayDone = true;
            break;

            case 'confirm_order':
                $Basket->createOrder();
                $Basket->emailConfirmation();

                // save the order ID and get the security code
                $ordId = $Basket->ordId;
                $secId = $Basket->securityCode($ordId);

                // show the thanks page
                $Basket->ShowOrderThanks();

                // start again with an empty basket
                $Basket = null;

                // wipe down the basket
                //$Basket = new Basket();


                // redirect to the show_payment_info page
                //header('Location: '.BASKET_URL."?command=show_payment_info&ord_id=$ordId&sec_id=$secId");
                $displayDone = true;
            break;

            case 'show_payment_info':
                // create a basket from the database and show payment info
                $newBasket = new Basket($_REQUEST['ord_id'], $_REQUEST['sec_id']);
                $newBasket->ShowPaymentInfo();
                $displayDone = true;
            break;

            case 'continue_shopping':
                $Basket->UpdateQuantities();
                $Categories = new Categories();
                $Categories->ShowDetails();
                $displayDone = true;
            break;

            case 'print_order':
                $ordId = $_REQUEST['ord_id'];
                $secureId = $_REQUEST['sec_id'];
                $newBasket = new Basket($ordId, $secureId);
                $newBasket->ShowBasket(BASKET_PRINT);
                $displayDone = true;
            break;

            case 'fax_order':
                $ordId = $_REQUEST['ord_id'];
                $secureId = $_REQUEST['sec_id'];
                $newBasket = new Basket($ordId, $secureId);
                $newBasket->ShowBasket(BASKET_FAX);
                $displayDone = true;
            break;

            case 'phone_order':
                $ordId = $_REQUEST['ord_id'];
                $secureId = $_REQUEST['sec_id'];
                $newBasket = new Basket($ordId, $secureId);
                $newBasket->ShowBasket(BASKET_PHONE);
                $displayDone = true;
            break;

            case 'view_order':

                $ordId = $_REQUEST['ord_id'];
                $secureId = $_REQUEST['sec_id'];
                $newBasket = new Basket($ordId, $secureId);

                $newBasket->ShowOrderForm();
                $displayDone = true;
            break;
        }


        // nothing display so far - do the default
        if (!$displayDone) {
            if ($Basket->isEmpty()) {
                $Categories = new Categories();
                $Categories->ShowDetails();
            } else {
                $Basket->showBasket(BASKET_EDIT);
            }
        }

    }

}


?>