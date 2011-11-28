<?php
// --------------------------------------------------------------------------
// ProcessRequest
//
// A class to handle a request from a submission
// --------------------------------------------------------------------------



class RequestProcessor
{

    // 'Commands' on the requset are processed in an order:
    // command  : this would come first
    // cmd_command  : commands from submit buttons like this would come next
    // cmd_command_x  : last in list would be commands from image buttson

    // This holds the LAST command found
    var $Command;

    // This holds all commands in the above order
    var $_commands;
    
    // gets populated by hasCommandStarting()
    var $commandEnding = null;

    // ----------------------------------------------------------------------
    // RequestProcessor
    //
    // The process request method reads $_REQUEST and takes appropiate
    // action. It is listed here as a place holder only
    // ----------------------------------------------------------------------
    function RequestProcessor()
    {
        $this->_GetCommand();
        $this->Process();
    }

    // ----------------------------------------------------------------------
    // Process - Abstract Method
    //
    // ----------------------------------------------------------------------
    function Process() {

    }

    // ----------------------------------------------------------------------
    // GetCommand
    //
    // Gets the command specified by the user. The command can come in one of
    // three ways
    // 1. as a hidden variable called "command"
    // 2. as a html button prefixed with "cmd_" eg "cmd_save_data"
    // 3. as a image button prefixed with "cmd_" and ending with "_x" and "_y"
    //    note: the _x and _y are added by the browser
    //
    // You can specify both a hidden variable and a "cmd_" button variable so
    // that if the user click on enter the hidden variable will be the default
    // otherwise the clicked on button with be actioned

    // Added action to store all commands in an array - rather than just the last
    // one
    // ----------------------------------------------------------------------
    function _GetCommand()
    {
        $this->Command = false; // default is false

        if (isset($_REQUEST['command'])) {
            $this->Command = $_REQUEST['command'];
            //$this->_commands[] = $this->Command;
            $this->_commands[$_REQUEST['command']] = $_REQUEST['command'];
        }

        // image buttons end with _x and _y so we need to stript this off
        $Endings = array ('_x', '_y');

        foreach($_REQUEST as $Field => $Value) {

            if (substr($Field, 0, 4) == 'cmd_') {

                $this->Command = substr($Field, 4);

                if (in_array(substr($this->Command, -2), $Endings)) {
                    $this->Command = substr($this->Command, 0, -2);
                }

                //$this->_commands[] = $this->Command;
                $this->_commands[$this->Command] = $Value;
            }
        }        
    }

    // ----------------------------------------------------------------------
    // hasCommand
    //
    // Checks the _commands array to see if the specified item exists
    // This is case insensitive
    // ----------------------------------------------------------------------
    function hasCommand($theCommand) {

        $Result = false;

        if (is_array($this->_commands)) {
            foreach ($this->_commands as $Command => $Value) {
                if (strcasecmp($Command, $theCommand) == 0) {
                    $Result = true;
                    break;
                }
            }
        }

        return $Result;
    }

    // ----------------------------------------------------------------------
    // commandValue
    //
    // Checks the _commands array to see if the specified item exists and
    // returns the command's value.
    //
    // note a command may exist, but have a logical value of false
    // returns null if command not defined
    // ----------------------------------------------------------------------
    function commandValue($theCommand) {

        $Result = null;

        if (is_array($this->_commands)) {
            foreach ($this->_commands as $Command => $Value) {
                if (strcasecmp($Command, $theCommand) == 0) {
                    $Result = $Value;
                    break;
                }
            }
        }

        return $Result;
    }
    
    
    // ----------------------------------------------------------------------
    // hasCommandStarting
    //
    // checks to see if there is a string in _commands that starts with the 
    // given $theCommand and saves the bit after $theCommand into 
    // $this->commandEnding
    // e.g. if you had a command "cmd_add_user_1" the test 
    //    $this->hasCommandStarting("add_user_");
    // would return true, and $this->commandEnding would be "1"
    // ----------------------------------------------------------------------
    function hasCommandStarting($theCommand)
    {
        $Result = false;

        if (is_array($this->_commands)) {
            $theCommandLen = strlen($theCommand);
            foreach ($this->_commands as $Command => $Value) {
                if (strncasecmp($Command, $theCommand, $theCommandLen) == 0) {
                    $this->commandEnding = substr($Command, $theCommandLen);
                    $Result = true;
                    break;
                }
            }
        }

        return $Result;                
    }
    

}


?>