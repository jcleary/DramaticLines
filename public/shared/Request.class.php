<?php
//**********************************************************
// Request
//**********************************************************
//
// Simple wrapper around $_REQUEST variables
//
// $req = new Request();
//
// $req->get("inputName");
// $req->get("stringInputName",   TYPE_STRING);
// $req->get("IntegerInputName",  TYPE_INTEGER);
// $req->get("yesNoInputName",    TYPE_BOOLEAN);
// $req->get("currencyInputName", TYPE_FLOAT);
//
// if ($req->exists("page")) {
//     echo "page was submitted";
// }

//**********************************************************

class Request

{

    var $_vars     = array(); // the form variables (upper case key)
    var $_varCount = 0;       // size of $_vars
    var $decodeHtmlEntities = false; // used to decide whether to decode html entities on get method

    //------------------------------------------------------------
    function Request($formArray = null)
    //
    // constructor
    //
    // parameters:
    // none
    //
    // returns:
    // none
    // -----------------------------------------------------------

    {
        if (is_null($formArray)) {
            $formArray = $_REQUEST;
        }


        foreach ($formArray as $variable => $value) {

            $value = $this->_formatValue($value);

            $this->_vars[strtoupper($variable)] = $value;
        }

        $this->_varCount = count($this->_vars);

    }

    //------------------------------------------------------------
    function _formatValue($value)
    //
    // preformats form variables (just trims at the moment)
    //
    // parameters:
    // $value: form value
    //
    // returns:
    // trimmed value of variable
    //------------------------------------------------------------

    {

        switch (TRUE) {

            case (is_string($value)):
               // just remove spaces and tabs
               // we may need \n from textareas
               $retValue = trim($value, " \t");
               // get rid of html
               $retValue = htmlentities($retValue);
               break;

            case (is_array($value)):
                foreach ($value as $key => $val){
                    $retValue[strtoupper($key)] = $this->_formatValue($val);
                }
                break;


            default:
                $retValue = $value;
                break;

        }

        return ($retValue);

    }

   //------------------------------------------------------------
    function exists($varName)
    //
    // returns if variable set
    //
    // parameters:
    // $VarName: form variable to check

    // returns:
    // boolean true/false
    //------------------------------------------------------------

    {
        return (array_key_exists(strtoupper($varName), $this->_vars));
    }


    //------------------------------------------------------------
    function get($varName, $type = null)
    //
    // returns value of form variable
    //
    // parameters:
    // $VarName: form variable to return (case insensitive)
    // $Type:    optional type -
    //
    // returns:
    // value of variable or null if not set
    //------------------------------------------------------------

    {
        $value = $this->_getFormVar($varName);

        switch (true) {

            case ($type == TYPE_INTEGER):
                $value = $this->_toInt($value);
                break;

            case ($type == TYPE_BOOLEAN):
                $value = $this->_toBool($value);
                break;

            case ($type == TYPE_FLOAT):
            case ($type == TYPE_DOUBLE):
                $value = $this->_toFloat($value);
                break;

            case ($type == TYPE_ARRAY):
                $value = $this->_toArray($value);
                break;

            case ($type == TYPE_STRING):
            case (is_null($type)):
            default:
                if ($this->decodeHtmlEntities) {
                    $value = html_entity_decode($value);
                }
                break;

        }

        return ($value);

    }

    //------------------------------------------------------------
    function _getFormVar($varName)
    //
    // returns value of form variable
    //
    // parameters:
    // $VarName: form variable to return (case insensitive)
    //
    // returns:
    // value of variable or null if not set
    //------------------------------------------------------------

    {

        $varName = strtoupper($varName);

        if ($this->exists($varName)) {
            $value = $this->_vars[$varName];
        } else {
            $value = null;
        }

        return ($value);

    }


    //------------------------------------------------------------
    function _toInt($value)
    //
    // converts to integer
    //------------------------------------------------------------

    {

        // remove commas and currency symbols
        // as with php anything after the value is ignored

        $value = str_replace(",", "", $value);
        $value = str_replace("$", "", $value);
        $value = str_replace("£", "", $value);

        $value = (int)$value;

        return ($value);

    }

    //------------------------------------------------------------
    function _toFloat($value)
    //
    // converts to float
    //------------------------------------------------------------

    {

        // remove commas and currency symbols
        // as with php anything after the value is ignored

        $value = str_replace(",", "", $value);
        $value = str_replace("$", "", $value);
        $value = str_replace("£", "", $value);

        $value = (float)$value;

        return ($value);

    }


    //------------------------------------------------------------
    function _toBool($value)
    //
    // converts to boolean
    // note it variable not set we return false *not* null
    //------------------------------------------------------------

    {

        if (is_null($value) || empty($value)) {
            return false;
        }

        $value = strtoupper($value{0});

        if (!empty($value)) {
            $value = @strpos("1YT", $value);
        }

        // force type
        return ($value !== false);

    }



    //------------------------------------------------------------
    function _toArray($value)
    //
    // converts to an array
    //------------------------------------------------------------
    {
        switch (true) {
            case is_null($value):
                $value = array();
                break;

            case is_array($value):
                // do noting
                break;

            default:
                $value = array($value);
                break;
        }

        return $value;
    }


}

?>