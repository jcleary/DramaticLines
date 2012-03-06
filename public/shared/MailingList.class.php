<?php
// **************************************************************************
// MailingList.class.php
//
// class to manage the mailing list
// **************************************************************************



include_once(SHARED . 'Base.class.php');
include_once(PHP_MAILER . 'class.phpmailer.php');



class MailingList extends Base
{

    // ----------------------------------------------------------------------
    // showJoinList
    //
    // shows the "join mailing list" page
    // ----------------------------------------------------------------------
    function showJoinList()
    {
        $xtpl = new XTemplate('join_mailing.xtpl', TEMPLATES);

        $this->parseErrorMessages($xtpl);

        parse_country_select($xtpl, 'GB');

        $xtpl->parse('main');
        return $xtpl->text('main');
    }



    // ----------------------------------------------------------------------
    // addMember
    //
    // adds the details of the new member to the list
    // ----------------------------------------------------------------------
    function addMember()
    {
        $name         = $_REQUEST['name'];
        $addressLine1 = $_REQUEST['address_line_1'];
        $addressLine2 = $_REQUEST['address_line_2'];
        $addressLine3 = $_REQUEST['address_line_3'];
        $addressLine4 = $_REQUEST['address_line_4'];
        $countryCode  = $_REQUEST['country_code'];
        $postcode     = $_REQUEST['postcode'];


        $sql  = "insert into mailing_list (created_date, name, address_line_1, address_line_2, address_line_3, address_line_4, country_code, postcode) ";
        $sql .= "values (";
        $sql .= "curdate(), ";
        $sql .= "'$name', ";
        $sql .= "'$addressLine1', ";
        $sql .= "'$addressLine2', ";
        $sql .= "'$addressLine3', ";
        $sql .= "'$addressLine4', ";
        $sql .= "'$countryCode', ";
        $sql .= "'$postcode')";

        mysql_query($sql);

        $emailTxt  = "Name:    $name\n";
        $emailTxt .= "Address: \n";
        $emailTxt .= "         $addressLine1\n";
        $emailTxt .= "         $addressLine2\n";
        $emailTxt .= "         $addressLine3\n";
        $emailTxt .= "         $addressLine4\n";
        $emailTxt .= "Postcode $postcode\n";
        $emailTxt .= "Country: " . getCountryName($countryCode) . " ($countryCode)\n";
        $emailTxt .= "Date:    " . date('D j M Y') . "\n";
        
        $transport = Swift_SmtpTransport::newInstance(EMAIL_FROM_SMPT, 465, 'ssl')
            ->setUsername(EMAIL_FROM_USERNAME)
            ->setPassword(EMAIL_FROM_PASSWORD);
        
        $mailer = Swift_Mailer::newInstance($transport);     
            
        $message = Swift_Message::newInstance()
            ->setSubject("New Mailing List Member")
            ->setFrom(array(EMAIL_FROM => EMAIL_FROM_NAME))
            ->setTo(array(
                EMAIL_FROM => EMAIL_FROM_NAME,
                EMAIL_ADMIN => EMAIL_ADMIN_NAME
                ))
            ->setBody($emailTxt);
                        
        $result = $mailer->send($message);            

    }




    // ----------------------------------------------------------------------
    // showThankYou
    //
    // shows the thank you page
    // ----------------------------------------------------------------------
    function showThankYou()
    {
        $xtpl = new XTemplate('join_mailing_thankyou.xtpl', TEMPLATES);

        $this->parseErrorMessages($xtpl);

        $xtpl->parse('main');
        return $xtpl->text('main');
    }



    // ----------------------------------------------------------------------
    // showList
    //
    // shows the mailing list in its entirety
    // ----------------------------------------------------------------------
    function showList()
    {


    }

}

?>