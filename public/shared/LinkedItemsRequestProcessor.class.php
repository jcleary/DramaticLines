<?php
// **************************************************************************
// LinkedItemsRequestProcessor
//
// request processor for linking items
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'LinkedItems.class.php');
include_once(SHARED . 'Request.class.php');


class LinkedItemsRequestProcessor extends RequestProcessor 
{
    
    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {   
        $request = new Request();
        $li = new LinkedItems();          
        
        switch($this->Command) {
            case 'delete_link':
                $li->DeleteLink($request->get('from_id'), $request->get('to_id'));
                $li->showLinkedItems();
            break;
            
            case 'add_link':
                $li->addLink($request->get('from_id'), $request->get('to_id'));
                $li->showLinkedItems();
            break;
            
            default:
                $li->showLinkedItems();
            break;
		}
    }
}


?>