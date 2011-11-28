<?php
// --------------------------------------------------------------------------
// Base.class.php
//
// a base class for all classes
// --------------------------------------------------------------------------



class Base
{
    var $_errorMessages = array();
    
    
    
    // ----------------------------------------------------------------------
    // addErrorMessage
    //
    // adds an error message to the array of error messages
    // ----------------------------------------------------------------------
    function addErrorMessage($message)
    {
        $this->_errorMessages[] = $message;
    }   
    
    
    
    // ----------------------------------------------------------------------
    // clearErrorMessages
    //
    // clears array of error messages
    // ----------------------------------------------------------------------
    function clearErrorMessages()
    {
        $this->_errorMessages = array();
    }
    
    
    
    // ----------------------------------------------------------------------
    // parseErrorMessages
    //
    // parses the array of error messages
    // ----------------------------------------------------------------------
    function parseErrorMessages(&$xtpl, $clearWhenDone = true)
    {        
        if ($this->hasErrors()) {
            foreach($this->_errorMessages as $message) {
                $xtpl->assign('errorMessage', $message);
                $xtpl->parse('main.error_messages.error');
            }
        
            $xtpl->parse('main.error_messages');
        }       
        
        if ($clearWhenDone) {
            $this->clearErrorMessages();
        } 
    }

    
    
    // ----------------------------------------------------------------------
    // parseErrorMessages
    //
    // parses the array of error messages
    // ----------------------------------------------------------------------
    function hasErrors()
    {
        return (count($this->_errorMessages) > 0);
    }
    
}

?>