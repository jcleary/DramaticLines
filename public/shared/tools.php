<?php
include_once(SHARED . "config.inc.php");

// connets to the database
function connect_db() {
    global $mysql_server, $mysql_username, $mysql_password, $mysql_database;
    $retval = FALSE;

    if (@mysql_connect($mysql_server, $mysql_username, $mysql_password)) {
        if (mysql_select_db($mysql_database)) {
            $retval = TRUE;
        } else {
            echo "count not select";
        }
    } else {
        echo "could not login";
    }
    return($retval);
}



function PreventResubmit()
{
    global $HTTP_GET_VARS;
    global $TimeArray;
    static $submitOk = false;


    if ($submitOk) {
        return true;
    }

    $retVal = true;

    if (!session_is_registered('TimeArray')) {
        session_register('TimeArray');
        $TimeArray = array();
    }


    if (in_array($HTTP_GET_VARS['time'], $TimeArray)) {
        $retVal = false;
    } else {
        $TimeArray[] = $HTTP_GET_VARS['time'];
    }

    return($retVal);
}



function upload_image($name, $imageRoot, $maxWidth, $maxHeight) {

    global $HTTP_POST_FILES;

    $OriginalName = $HTTP_POST_FILES[$name]['name'];
    $TempFile = $HTTP_POST_FILES[$name]['tmp_name'];

    copy_resize_image($TempFile, $imageRoot.$OriginalName, $maxWidth, $maxHeight);
}



function copy_resize_image($source, $target, $max_width, $max_height) {

   $image = ImageCreateFromJpeg($source);

   $row = GetImageSize($source);
   $src_width  = $row[0];
   $src_height = $row[1];


   if (($src_width / $max_width) > ($src_height / $max_height)) {
      $target_height = round($src_height * ($max_width / $src_width));
      $target_width = $max_width;
   } else {
      $target_height = $max_height;
      $target_width = round($src_width * ($max_height / $src_height));
   }

   if ($image > 0) {                        # if the file was read ok

      # resize the image into a new object
      $target_image = ImageCreate($target_width, $target_height);
      ImageCopyResized($target_image, $image, 0, 0, 0, 0, $target_width, $target_height, $src_width, $src_height);
      imageJPEG($target_image, $target);
   }

}

// -------------------------------------------------------------------------
// is_email
//
// function to check format of an email address
// -------------------------------------------------------------------------
function is_email($email){
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}


// --------------------------------------------------------------------------
// is_postcode
//
// Validates a postcode.
//
// For a full explination of the rules used, look at:
//    http://www.mailsorttechnical.com/frequentlyaskedquestions.cfm
// --------------------------------------------------------------------------
function is_postcode( $Postcode ) {
    // convert the postcode to a list of A's and N's (A for alpha, N for numeric)
    $Search = array("/[A-Z,a-z]/", "/[0-9]/", "/ /");
    $Replace = array("A", "N", "");
    $OutPostcode = preg_replace($Search, $Replace, $Postcode);                   // search and replace
    $OutPostcode = substr($OutPostcode, 0, -3) . ' ' . substr($OutPostcode, -3); // put space back in

    // this is a list of valid postcode formats
    $ValidFormat = array('AN NAA', 'ANN NAA', 'AAN NAA', 'AANN NAA', 'ANA NAA', 'AANA NAA');

    $RetVal = (in_array($OutPostcode, $ValidFormat)) || ($Postcode == 'GIR 0AA') ; // this postcode is a one-off for GiroBank

    return($RetVal);
}


function parse_country_select(&$xtpl, $currentValue)
{
    $queryArray = array(
       "select * from countries where cou_shortlist is not null order by cou_shortlist",
       "select '' cou_id, '- - - - - - - - - - - - - - - - - - - - -' cou_name",
       "select * from countries order by cou_name");

    $selectDone = false;

    foreach($queryArray as $queryString) {
        $qry = mysql_query($queryString);
        while ($row = mysql_fetch_array($qry)) {
            $xtpl->assign('countryCode', $row['cou_id']);
            $xtpl->assign('countryName', $row['cou_name']);
            if (!$selectDone && $row['cou_id'] == $currentValue) {
                $xtpl->parse('main.country.selected');
                $selectDone = true;
            }
            $xtpl->parse('main.country');
        }
    }
}

// --------------------------------------------------------------------------
// getCountryName
//
// --------------------------------------------------------------------------
function getCountryName($countryCode)

{
    $sql = "select cou_name from countries where cou_id = '$countryCode'";
    $qry = mysql_query($sql);
    $row = mysql_fetch_array($qry);

    return $row['cou_name'];
}

?>
