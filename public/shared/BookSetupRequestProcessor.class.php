<?php
// **************************************************************************
// BookSetupRequestProcessor
//
// request processor for the shopping basket
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'Basket.class.php');
include_once(SHARED . 'categories.class.php');


class BookSetupRequestProcessor extends RequestProcessor 
{
    
    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {     
        switch($this->Command) {
			case 'edit_title':
				$Product = new Product($_REQUEST['title_id']);          
				$Product->ShowEdit();
			break;            
				
			case 'update_title':
				$Product = new Product($_REQUEST['title_id']);          
				$Product->Update();
				$Categories = new Categories();
				$Categories->ShowDetails(false);
			break;            
		
			case 'add_title':
				connect_db();
				mysql_query('insert into titles (cat_id, ord) values ('.$_REQUEST['cat_id'].', 99)');
				echo mysql_error();
				$id = mysql_insert_id();
				$Product = new Product($id);          
				$Product->ShowEdit();
			break;            
				
			default:
			case 'show_categories':
				$Categories = new Categories();
				$Categories->ShowDetails(false);
				break;
		
			}
    }
}


?>