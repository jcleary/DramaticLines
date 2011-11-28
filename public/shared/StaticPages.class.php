<?php
// **************************************************************************
// StaticPages
//
// class to show static pages such as the home page and about us
// **************************************************************************



class StaticPages
{


    // ----------------------------------------------------------------------
    // showHomePage
    //
    // displays the home page
    // ----------------------------------------------------------------------
    function showHomePage()
    {
        $xtpl = new XTemplate('index.xtpl', TEMPLATES);

        if (SYSTEM_TYPE != SYSTEM_LIVE) {
            $xtpl->parse('main.noRobots');
        }

        $xtpl->parse('main');
        $xtpl->out('main');
    }



    // ----------------------------------------------------------------------
    // showAboutUsPage
    //
    // shows the about us page
    // ----------------------------------------------------------------------
    function showAboutUsPage()
    {
        $xtpl = new XTemplate('about_us.xtpl', TEMPLATES);

        $xtpl->parse('main');
        $xtpl->out('main');
    }


    // ----------------------------------------------------------------------
    // showPaymentReceivedPage
    //
    // shows the payment received page
    // ----------------------------------------------------------------------
    function showPaymentReceivedPage()
    {
        $xtpl = new XTemplate('payment_received.xtpl', TEMPLATES);

        $xtpl->parse('main');
        $xtpl->out('main');
    }



}



?>