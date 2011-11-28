<?php

class Page

{
    var $_body = '';

    //------------------------------------------------------------
    // setBody($body)
    //------------------------------------------------------------
    function setBody($body)

    {
        $this->_body = $body;
    }

    //------------------------------------------------------------
    // show()
    //------------------------------------------------------------
    function show()

    {
        if (!headers_sent()) {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT', true);
            header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP/1.1
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        } else {
            echo 'Headers sent';
        }

        echo $this->_generateHeader();
        echo $this->_body;
        echo $this->_generateFooter();
    }

    //------------------------------------------------------------
    // _generateHeader()
    //------------------------------------------------------------
    function _generateHeader()

    {
        $xtpl = new XTemplate('Header.xtpl');

        $xtpl->parse('main');

        return $xtpl->text('main');
    }

    //------------------------------------------------------------
    // _generateFooter()
    //------------------------------------------------------------
    function _generateFooter()

    {
        $xtpl = new XTemplate('Footer.xtpl');

        $xtpl->parse('main');

        return $xtpl->text('main');
    }

}

?>
