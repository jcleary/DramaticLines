<?php
// --------------------------------------------------------------------------
// basket.class.php
// --------------------------------------------------------------------------
// This class manages the whole shopping basket process, from adding items,
// removing or changing quantities, capturing deliver and invoice details,
// taking payment and sending confirmation emails


// Includes
include_once(PHP_MAILER . 'class.phpmailer.php');
include_once(SHARED . 'dl_general.inc.php');
include_once(SHARED . 'Base.class.php');
include_once(SHARED . 'messages.inc.php');


// basket printing mode
define('BASKET_EDIT',                    1);
define('BASKET_PRINT',                   2);
define('BASKET_FAX',                     3);
define('BASKET_PHONE',                   4);
define('BASKET_CONFIRM',                 5);
define('BASKET_TXT_CONFIRMATION_EMAIL',  6);
define('BASKET_HTML_CONFIRMATION_EMAIL', 7);
define('BASKET_SETUP_VIEW_ORDER',        8);

define ('PAYMENT_METHOD_ONLINE',        'ONLINE');
define ('PAYMENT_METHOD_FAX',           'FAX');
define ('PAYMENT_METHOD_PHONE',         'PHONE');
define ('PAYMENT_METHOD_PRINT',         'PRINT');
define ('PAYMENT_METHOD_OFFICAL_ORDER', 'OFFICAL');
define ('PAYMENT_METHOD_UNKNOWN',       'UNKNOWN');


class Basket extends Base {

    var $Items = array();

    var $ordId;

    var $itemsTotal;
    var $pAndP;
    var $orderTotal;

    var $name;
    var $addressLine1;
    var $addressLine2;
    var $addressLine3;
    var $addressLine4;
    var $countryCode = 'GB'; // default to GB
    var $postcode;

    var $tel;
    var $email;

    var $paymentMethod;

    var $createdDate;

    var $shippingSaved = false;
    var $paymentSaved = false;


    // ----------------------------------------------------------------------
    // Basket
    //
    // Constructor. Used to re-create a basket from the database
    // ----------------------------------------------------------------------
    function Basket($orderId = null, $securityCode = null)
    {
        // if an order id is provided then try to read order from database
        if (!is_null($orderId)) {

            $qry = mysql_query("select * from orders where ord_id = $orderId");
            if ($row = mysql_fetch_array($qry)) {

                if ($securityCode == $row['sec_id']) {

                    $this->ordId = $orderId;

                    $this->name             = $row['name'];
                    $this->addressLine1     = $row['address_line_1'];
                    $this->addressLine2     = $row['address_line_2'];
                    $this->addressLine3     = $row['address_line_3'];
                    $this->addressLine4     = $row['address_line_4'];
                    $this->countryCode      = $row['country_code'];
                    $this->postcode         = $row['postcode'];

                    $this->tel              = $row['tel'];
                    $this->email            = $row['email'];

                    $this->paymentMethod    = $row['payment_method'];

                    $this->createdDate      = date('D j M Y', strtotime($row['created_date']));

                    $this->itemsTotal       = $row['items_total'];
                    $this->pAndP            = $row['p_and_p'];
                    $this->orderTotal       = $row['order_total'];


                    $qry = mysql_query("select * from order_items where ord_id = $orderId");
                    while ($row = mysql_fetch_array($qry)) {
                        $newProduct = new Product($row['product_code']);
                        $newProduct->Qty         = $row['qty'];
                        $newProduct->Description = $row['description'];
                        $newProduct->ProductCost = $row['unit_cost'];

                        // add item to the basket
                        $this->Items[] = $newProduct;
                    }
                }
            }
        }

    }


    // ----------------------------------------------------------------------
    // AddItem
    //
    // Add an item to the basket
    // ----------------------------------------------------------------------
    function addItem($Product)
    {

        if (PreventResubmit()) {
            // first of all check if the product is already in the basket
            $Found = false;
            for ($i=0; $i < count($this->Items); $i++) {
                if ($this->Items[$i]->ProductCode == $Product->ProductCode) {
                    $this->Items[$i]->Qty += $Product->Qty;
                    $Found = true;
                }
            }

            if (!$Found) {
                $this->Items[] = $Product;
            }
        }
    }




    // ----------------------------------------------------------------------
    // ChangeItemQty
    //
    // Change the quantity of a product. Changing to zero will delete
    // ----------------------------------------------------------------------
    function changeItemQty($ProductCode, $Qty)
    {
        for ($i=0; $i < count($this->Items); $i++) {
            if ($this->Items[$i]->ProductCode == $ProductCode) {
                $this->Items[$i]->Qry = $Qty;
            }
        }
    }



    // ----------------------------------------------------------------------
    // ShowBasket
    //
    // Show the shopping basket
    // ----------------------------------------------------------------------
    function showBasket($basketMode, $returnText = false)
    {

        $req = new Request();

        // if the user changed the country on the basket page
        // use that instead of the stored value
        if ($req->get('country_code')) {
            $this->countryCode = $req->get('country_code');
        }

        switch ($basketMode) {
            case BASKET_TXT_CONFIRMATION_EMAIL:
                $templateFilename = 'email/order_confirmation_txt.xtpl';
            break;

            case BASKET_HTML_CONFIRMATION_EMAIL:
                $templateFilename = 'email/order_confirmation_html.xtpl';
            break;

            case BASKET_PRINT:
            case BASKET_FAX:
            case BASKET_PHONE:
                $templateFilename = 'printable_order_form.xtpl';
            break;

            case BASKET_CONFIRM:
                $templateFilename = 'confirm_order.xtpl';
            break;

            case BASKET_SETUP_VIEW_ORDER:
                $templateFilename = 'setup/view_order.xtpl';
            break;

            case BASKET_EDIT:
            default:
                $templateFilename = 'show_basket.xtpl';
            break;
        }

        $xtpl = new XTemplate($templateFilename, TEMPLATES);

        $xtpl->assign('name', $this->name);
        $xtpl->assign('addressLine1', $this->addressLine1);
        $xtpl->assign('addressLine2', $this->addressLine2);
        $xtpl->assign('addressLine3', $this->addressLine3);
        $xtpl->assign('addressLine4', $this->addressLine4);
        $xtpl->assign('postcode', $this->postcode);
        $xtpl->assign('tel', $this->tel);
        $xtpl->assign('email', $this->email);
        $xtpl->assign('ordId', $this->ordId);
        $xtpl->assign('createdDate', $this->createdDate);

        // don't use 'countryName' as the parse dropdown uses the same
        // name for its dropdown description and you'll end up
        // with Zimbabwe here
        $xtpl->assign('addressCountryName', getCountryName($this->countryCode));

        $AllBookTotal = 0;

        foreach($this->Items as $Item) {
            $BookTotal = $Item->Qty * $Item->ProductCost;

            $AllBookTotal += $BookTotal;

            //$xtpl->assign($Item->_Record);
            $xtpl->assign('Qty', $Item->Qty);
            $xtpl->assign('title', $Item->Description);
            $xtpl->assign('price', $Item->ProductCost);
            $xtpl->assign('title_id', $Item->ProductCode);
            $xtpl->assign('BookTotal', number_format($BookTotal, 2));

            $xtpl->parse('main.title');
        }

        $AllShippingTotal = $this->getPostageCost();

        $xtpl->assign('AllBookTotal', number_format($AllBookTotal, 2));
        $xtpl->assign('AllShippingTotal', number_format($AllShippingTotal, 2));
        $xtpl->assign('Total', number_format($AllShippingTotal+$AllBookTotal, 2));

        switch ($basketMode) {
            case BASKET_PRINT:
                $xtpl->parse('main.dramatic_address');
                $xtpl->parse('main.post_instructions');
                $xtpl->parse('main.credit_card_details');
            break;

            case BASKET_FAX:
                $xtpl->parse('main.fax_instructions');
                $xtpl->parse('main.credit_card_details');
            break;

            case BASKET_PHONE:
                $xtpl->parse('main.phone_instructions');
            break;
        }

        // default is GB
        //if ($this->countryCode == 'GB') {
        //    $xtpl->parse('main.ukRatesIndicator');
        //    $xtpl->parse('main.ukRatesNote');
        //}

        parse_country_select(&$xtpl, $this->countryCode);

        // *********************
        // payment method descr
        // *********************

        $xtpl->assign('paymentMethodDescr', $this->_getPaymentMethodDescr($this->paymentMethod));

        $xtpl->parse('main');

        if ($returnText) {
            return $xtpl->text('main');
        } else {
            $xtpl->out('main');
        }

    }


    // ----------------------------------------------------------------------
    // ShowShippingInfo
    //
    // Show the shipping info
    // ----------------------------------------------------------------------
    function showShippingInfo()
    {
        $xtpl = new XTemplate('shipping_info.xtpl', TEMPLATES);

        $this->parseErrorMessages($xtpl);

        $xtpl->assign('TIME', TIME);

        $xtpl->assign('name',           $this->name);
        $xtpl->assign('address_line_1', $this->addressLine1);
        $xtpl->assign('address_line_2', $this->addressLine2);
        $xtpl->assign('address_line_3', $this->addressLine3);
        $xtpl->assign('address_line_4', $this->addressLine4);
        $xtpl->assign('postcode',       $this->postcode);
        $xtpl->assign('tel',            $this->tel);
        $xtpl->assign('email',          $this->email);

        parse_country_select($xtpl, $this->countryCode);

        $xtpl->parse('main');
        $xtpl->out('main');
    }



    // ----------------------------------------------------------------------
    // getPostageCost
    //
    // work out the p&p costs
    // ----------------------------------------------------------------------
    function getPostageCost()

    {

        $shippingTotal = 0;
        //$weightTotal = 0;

        foreach($this->Items as $Item) {
            $shippingTotal += $Item->Qty * $Item->ShippingCost;
            //$weightTotal   += $Item->Qty * $Item->Weight;
        }

        if ($this->countryCode == 'GB') {
            $shippingTotal = min($shippingTotal, MAX_UK_SHIPPING);
        } else {
            $shippingTotal = $shippingTotal * OVERSEAS_SHIPPING_MULTIPLIER;
        }

        //else {
        //  $qry = mysql_query("select * from countries, postal_zones where cou_id = '$this->countryCode' and cou_zone = zon_id");
        //    if ($row = mysql_fetch_array($qry)) {
        //        $fixedCharge = $row['zon_fixed_charge'];
        //        $pricePerKilo = $row['zon_price_per_kilo'];
        //
        //        $retVal = $fixedCharge + (($weightTotal / 1000) * $pricePerKilo);
        //    }
        //}

        return $shippingTotal;
    }




    // ----------------------------------------------------------------------
    // showPaymentOptions
    //
    // Show the payment method options
    // ----------------------------------------------------------------------
    function showPaymentOptions()

    {

        $paypalAllowed = false;

        $qry = mysql_query("select cou_paypal from countries where cou_id = '$this->countryCode'");

        if ($row = mysql_fetch_array($qry)) {
            $paypalAllowed = $row['cou_paypal'];
        } else {
            die("can't find country");
        }

        $xtpl = new XTemplate('payment_info.xtpl', TEMPLATES);

        if ($paypalAllowed) {
            $xtpl->parse('main.paypal');
        }

        $xtpl->parse('main.' . $this->paymentMethod . '_selected');

        $xtpl->parse('main');
        $xtpl->out('main');
    }


    // ----------------------------------------------------------------------
    // ParseBasketSummary
    //
    // takes a template and parses the basket summary
    // ----------------------------------------------------------------------
    function parseBasketSummary(&$xtpl)
    {
        $totalQty = 0;

        foreach ($this->Items as $Item) {
            $totalQty += $Item->Qty;
        }

        switch ($totalQty) {
            case 0:
                $xtpl->parse('main.empty_basket');
            break;

            case 1:
                $xtpl->assign('basket_count', $totalQty);
                $xtpl->parse('main.full_basket');
            break;

            default: // more that 1
                $xtpl->assign('basket_count', $totalQty);
                $xtpl->parse('main.full_basket.items');
                $xtpl->parse('main.full_basket');
            break;
        }

    }



    // ----------------------------------------------------------------------
    // UpdateQuantities
    //
    // update the quantities from the update quantity form
    // ----------------------------------------------------------------------
    function updateQuantities()
    {
        global $HTTP_GET_VARS;

        for($i=0; $i < count($this->Items); $i++) {
            $QtyField = 'qty_'.$this->Items[$i]->ProductCode;
            if (isset($HTTP_GET_VARS[$QtyField])) {
                $this->Items[$i]->Qty = $HTTP_GET_VARS[$QtyField];
            }
        }
        $this->RemoveEmpties();
    }



    // ----------------------------------------------------------------------
    // RemoveEmpties
    //
    // remove any items where the qty is zero
    // ----------------------------------------------------------------------
    function removeEmpties()
    {
        $NewItems = array();

        for($i=0; $i < count($this->Items); $i++) {
            if ($this->Items[$i]->Qty > 0) {
                $NewItems[] = $this->Items[$i];
            }
        }

        $this->Items = $NewItems;
    }



    // ----------------------------------------------------------------------
    // saveShippingInfo
    //
    //
    // ----------------------------------------------------------------------
    function saveShippingInfo()
    {
        $this->name          = $_REQUEST['name'];
        $this->addressLine1  = $_REQUEST['address_line_1'];
        $this->addressLine2  = $_REQUEST['address_line_2'];
        $this->addressLine3  = $_REQUEST['address_line_3'];
        $this->addressLine4  = $_REQUEST['address_line_4'];
        $this->countryCode   = $_REQUEST['country_code'];
        $this->postcode      = $_REQUEST['postcode'];
        $this->tel           = $_REQUEST['tel'];
        $this->email         = $_REQUEST['email'];
        $this->shippingSaved = true;

    }



    // ----------------------------------------------------------------------
    // savePaymentMethod
    //
    //
    // ----------------------------------------------------------------------
    function savePaymentMethod()
    {
        $this->paymentMethod = $_REQUEST['payment_method'];
        $this->paymentSaved  = true;
    }



    // ----------------------------------------------------------------------
    // createOrder
    //
    // creates the order in the database
    // ----------------------------------------------------------------------
    function createOrder()
    {
        //if (PreventResubmit()) {

            // create the order
            $sql = 'insert into orders (sec_id, created_date, name, address_line_1, address_line_2, address_line_3, address_line_4, country_code, postcode, tel, payment_method, email) ';
            $sql .= 'values (' ;
            $sql .= rand(1000000, 9999999) . ',';
            $sql .= 'curdate(), "';
            $sql .= $this->name . '", "';
            $sql .= $this->addressLine1 . '", "';
            $sql .= $this->addressLine2 . '", "';
            $sql .= $this->addressLine3 . '", "';
            $sql .= $this->addressLine4 . '", "';
            $sql .= $this->countryCode . '", "';
            $sql .= $this->postcode . '", "';
            $sql .= $this->tel . '", "';
            $sql .= $this->paymentMethod . '", "';
            $sql .= $this->email . '")';
            mysql_query($sql);
            //echo mysql_error();
            //die();

            // get the order ID
            $this->ordId = mysql_insert_id();

            // reset the counters
            $AllBookTotal = 0;

            // loop through the order
            foreach($this->Items as $Item) {
                $BookTotal = $Item->Qty * $Item->ProductCost;

                $AllBookTotal += $BookTotal;


                $sql = 'insert into order_items (ord_id, product_code, description, unit_cost, qty, item_total) values (';
                $sql .= $this->ordId . ',"';
                $sql .= $Item->ProductCode . '", "';
                $sql .= $Item->Description . '", ';
                $sql .= $Item->ProductCost . ', ';
                $sql .= $Item->Qty . ', ';
                $sql .= $BookTotal . ')';
                mysql_query($sql);
            }


            // get shipping costs
            $AllShippingTotal = $this->getPostageCost();

            // update the order record with the totals
            $sql  = 'update orders set ';
            $sql .= 'items_total = ' . $AllBookTotal . ', ';
            $sql .= 'p_and_p = ' . $AllShippingTotal . ', ';
            $sql .= 'order_total = ' . ($AllShippingTotal + $AllBookTotal) ;
            $sql .= ' where ord_id = ' . $this->ordId;

            mysql_query($sql);

            $this->orderTotal = $AllShippingTotal + $AllBookTotal;
        //}
    }

    // ----------------------------------------------------------------------
    // emailConfirmation
    //
    //
    // ----------------------------------------------------------------------
    function emailConfirmation()
    {
        $htmlConfirmation = $this->showBasket(BASKET_HTML_CONFIRMATION_EMAIL, true);
        $txtConfirmation = $this->showBasket(BASKET_TXT_CONFIRMATION_EMAIL, true);


        $mailer = new PhpMailer();
        $mailer->From = EMAIL_FROM;
        $mailer->FromName = EMAIL_FROM_NAME;

        $mailer->AddAddress($this->email, $this->name);
        $mailer->AddBCC(EMAIL_FROM, EMAIL_FROM_NAME);

        $mailer->IsHTML(true);

        // careful with the wording as my copy of Outlook considers some variations to be junk mail
        $mailer->Subject = "Your Dramatic Lines order ($this->ordId)";
        $mailer->Body    = $htmlConfirmation;
        $mailer->AltBody = $txtConfirmation;

        $mailer->IsSMTP();                       // set mailer to use SMTP
        $mailer->Host = EMAIL_FROM_SMPT;         // specify main and backup server
        $mailer->SMTPAuth = true;                // turn on SMTP authentication
        $mailer->Username = EMAIL_FROM_USERNAME; // SMTP username
        $mailer->Password = EMAIL_FROM_PASSWORD; // SMTP password

        if(!$mailer->Send())
        {
           echo "Message could not be sent. <p>";
           echo "Mailer Error: " . $mailer->ErrorInfo;
           exit;
        }

    }



    // ----------------------------------------------------------------------
    // _securityCode
    //
    // returns the security code for the given orderId
    // ----------------------------------------------------------------------
    function securityCode($orderId)
    {
        $retVal = "not_found";
        if ($orderId) {
            $qry = mysql_query("select * from orders where ord_id = $orderId");
            echo mysql_error();
            if ($row = mysql_fetch_array($qry)) {
                $retVal = $row['sec_id'];
            }
        }

        return $retVal;
    }



    // ----------------------------------------------------------------------
    // isEmpty
    //
    // returns true if the basket is empty
    // ----------------------------------------------------------------------
    function isEmpty()
    {
        return empty($this->Items);

    }



    // ----------------------------------------------------------------------
    // showGetOrderNo
    //
    // shows the order no entry page. For use in SETUP only
    // ----------------------------------------------------------------------
    function showGetOrderNo()
    {
        $xtpl = new XTemplate('get_order_no.xtpl', SETUP_TEMPLATES);

        $xtpl->parse('main');
        $xtpl->out('main');
    }


    // ----------------------------------------------------------------------
    // validateShippingInfo
    //
    // checks thay have supplied the minimum info
    // ----------------------------------------------------------------------
    function validateShippingInfo()
    {
        $this->clearErrorMessages();

        if (empty($this->name)) {
            $this->addErrorMessage(ERROR_NAME_MANDATORY);
        }

        if (empty($this->addressLine1)) {
            $this->addErrorMessage(ERROR_1ST_LINE_ADDRESS_MANDATORY);
        }

        if (empty($this->countryCode)) {
            $this->addErrorMessage(ERROR_COUNTRY_MANDATORY);
        } elseif ($this->countryCode == 'GB') {
            if (empty($this->postcode)) {
                $this->addErrorMessage(ERROR_UK_POSTCODE_MANDATORY);
            } elseif (!is_postcode($this->postcode)) {
                $this->addErrorMessage(ERROR_POSTCODE_INVALD);
            }
        }

        if (empty($this->email)) {
            $this->addErrorMessage(ERROR_EMAIL_MANDATORY);
        } elseif (!is_email($this->email)) {
            $this->addErrorMessage(ERROR_EMAIL_INVALID);
        }
    }


    // ----------------------------------------------------------------------
    // ShowPaymentInfo
    //
    // This used to show the payment info options, but now it shows the
    // confirmed order screen and instructions on how to pay.
    // ----------------------------------------------------------------------
    function ShowPaymentInfo()
    {
        switch ($this->paymentMethod)
        {
            case PAYMENT_METHOD_ONLINE:
                $mode = BASKET_CONFIRM;
            break;

            default:
                $mode = BASKET_PHONE;
            break;
        }
        $this->ShowBasket($mode);
    }



    // ----------------------------------------------------------------------
    // ShowOrderForm
    //
    //
    // ----------------------------------------------------------------------
    function ShowOrderForm()
    {
        $this->ShowBasket(BASKET_PHONE);
    }


    // ----------------------------------------------------------------------
    // ShowOrderThanks
    //
    //
    // ----------------------------------------------------------------------
    function ShowOrderThanks()
    {
        $xtpl = new XTemplate('order_thank_you.xtpl', TEMPLATES);

        $xtpl->assign('ordId',      $this->ordId);
        $xtpl->assign('secId',      $this->securityCode($this->ordId));
        $xtpl->assign('orderTotal', $this->orderTotal);

        $xtpl->assign('paymentMethodDescr', $this->_getPaymentMethodDescr($this->paymentMethod));

        switch ($this->paymentMethod) {
            case PAYMENT_METHOD_FAX:
            case PAYMENT_METHOD_PHONE:
            case PAYMENT_METHOD_PRINT:
                $xtpl->parse('main.print_post_fax');
            break;

            case PAYMENT_METHOD_ONLINE:
                $xtpl->parse('main.online');
            break;

            case PAYMENT_METHOD_OFFICAL_ORDER:
                $xtpl->parse('main.offical');
            break;

            case PAYMENT_METHOD_UNKNOWN:
            break;
        }

        $xtpl->parse('main');
        $xtpl->out('main');
    }

    // ----------------------------------------------------------------------
    // _getPaymentMethodDescr
    //
    //
    // ----------------------------------------------------------------------
    function _getPaymentMethodDescr($method)
    {
        $retVal = "";
        $paymentMethodDescrArray = array(PAYMENT_METHOD_ONLINE        => 'pay on-line using PayPal',
                                         PAYMENT_METHOD_FAX           => 'fax payment details through',
                                         PAYMENT_METHOD_PRINT         => 'print your order form and post payment through',
                                         PAYMENT_METHOD_PHONE         => 'phone though your payment details',
                                         PAYMENT_METHOD_OFFICAL_ORDER => 'raise an official order');
        if (array_key_exists($method, $paymentMethodDescrArray)) {
            $retVal = $paymentMethodDescrArray[$method];
        }
        return $retVal;
    }
}




?>
