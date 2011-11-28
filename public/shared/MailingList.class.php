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

        $mailer = new PhpMailer();
        $mailer->From = EMAIL_FROM;
        $mailer->FromName = EMAIL_FROM_NAME;

        $mailer->AddAddress(EMAIL_FROM, EMAIL_FROM_NAME);
        $mailer->AddAddress(EMAIL_ADMIN, EMAIL_ADMIN_NAME);

        $mailer->IsHTML(false);
        $mailer->Subject = "New Mailing List Member";
        $mailer->Body    = $emailTxt;

        $mailer->IsSMTP();                       // set mailer to use SMTP
        $mailer->Host = EMAIL_FROM_SMPT;         // specify main and backup server
        $mailer->SMTPAuth = true;                // turn on SMTP authentication
        $mailer->Username = EMAIL_FROM_USERNAME; // SMTP username
        $mailer->Password = EMAIL_FROM_PASSWORD; // SMTP password

        if(!$mailer->Send())
        {
           echo "Message could not be sent. <p>";
           echo "Mailer Error: " . $mailer->ErrorInfo;
           exit;
        }

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