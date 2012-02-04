<?php

/**
 * A base class for all classes
 * 
 * @author John Cleary
  */
class Base
{
    private $_errorMessages = array();
    
    /**
     * Adds an error message to the array of error messages
     * 
     * @param string $message
     */
    function addErrorMessage($message)
    {
        $this->_errorMessages[] = $message;
    }   
    
    /**
     * Clears array of error messages
     * 
     */
    function clearErrorMessages()
    {
        $this->_errorMessages = array();
    }
    
    /**
     * parses the array of error messages
     * 
     * @param $xtpl
     * @param $clearWhenDone
     */
    function parseErrorMessages(XTemplate $xtpl, $clearWhenDone = true)
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
    
    /**
     * Returns true if there are any error messages
     * 
     * @return bool
     */
    function hasErrors()
    {
        return (count($this->_errorMessages) > 0);
    }
    
}

