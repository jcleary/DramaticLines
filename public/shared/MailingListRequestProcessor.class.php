<?php
// **************************************************************************
// MailingListRequestProcessor
//
// request processor for the shopping basket
// **************************************************************************



include_once(SHARED . 'RequestProcessor.class.php');
include_once(SHARED . 'MailingList.class.php');


class MailingListRequestProcessor extends RequestProcessor 
{
    
    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {     
        switch($this->Command) {
			case 'join_mailing':
				$mailing = new MailingList();
				if (PreventResubmit()) {
				    $mailing->addMember();
				}
				echo $mailing->showThankYou();
			break;            
				
			default:
				$mailing = new MailingList();
				echo $mailing->showJoinList();
			break;
		
		}
    }
}


?>