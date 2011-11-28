<?php
// **************************************************************************
// PostalZonesRequestProcessor
//
// request processor for postal zones
// **************************************************************************


include_once(SHARED . 'PostalZones.class.php');
include_once(SHARED . 'RequestProcessor.class.php');



class PostalZonesRequestProcessor extends RequestProcessor 
{
    
    // ----------------------------------------------------------------------
    // process
    //
    // process method - runs automatically after object is created
    // ----------------------------------------------------------------------
    function process()
    {     
		$postalZones = new PostalZones();
		        
        switch($this->Command) {
			case 'change_zones':
				$postalZones->changeZones();
				$postalZones->showCountries();
			break;		
		
            case 'show_zones':
				$postalZones->showZones();
			break;
			
            case 'show_countries':
			default:
                $postalZones->showCountries();
            break;                
        }                
        
    }
    
}


?>