<?php
// **************************************************************************
// OrderRequestProcessor
//
// request processor for the shopping basket
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'Basket.class.php');



class OrderRequestProcessor extends RequestProcessor 
{
    
    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {     
        switch($this->Command) {
            
            case 'view_order':			
                $ordId = $_REQUEST['ord_id'];
				$qry = mysql_query("select * from orders where ord_id = $ordId");
				if ($row = mysql_fetch_array($qry)) {				
					$secureId = $row['sec_id']; 
					$newBasket = new Basket($ordId, $secureId);
					$newBasket->ShowBasket(BASKET_SETUP_VIEW_ORDER); 
				} else {
					$staticPages = new StaticPages();					
					$staticPages->showGetOrderNo();
				}				
            break;
                        
			default:
				$newBasket = new Basket();
				$newBasket->showGetOrderNo();
            break;
        }                
        
    }
    
}


?>