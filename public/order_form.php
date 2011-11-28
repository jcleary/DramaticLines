 
<html>
<head>
<title>Dramatic Lines Publishers</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="css/dl_general.css">
</head>
<body onLoad="" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF" background="images/book_covers/book_wp.gif">
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr valign="middle"> 
    <td rowspan="2" width="101"><img src="images/spacer.gif" width="101" height="8"></td>
    <td height="5"> <!-- #BeginLibraryItem "/Library/logo table.lbi" --><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td rowspan="3" width="10"><img src="images/spacer.gif" width="1" height="1"></td>
          <td height="10"><img src="images/spacer.gif" width="1" height="1"></td>
          <td rowspan="3" width="10"><img src="images/spacer.gif" width="1" height="1"></td>
        </tr>
        <tr> 
          <td><img src="images/logos/logo.gif" width="393" height="40" border="0"></td>
        </tr>
        <tr> 
          <td> 
            <table width="100%" border="0" cellspacing="0" cellpadding="0" background="images/logo_underline.gif" height="6">
              <tr> 
                <td><img src="images/spacer.gif" width="1" height="1"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table><!-- #EndLibraryItem --></td>
  </tr>
  <tr> 
    <td valign="top"> 
      <table width="100%" border="0" cellspacing="1" cellpadding="10" height="100%">
        <tr> 
          <td colspan="2" valign="top"> 
            <?
          if ($submit_uk || $submit_non_uk) {
		  ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td align="right"><font size="+2">Order Form</font><br><?
				if($submit_uk) {
				   echo "UK"; 
				} else {
				   echo "Non-UK";
				}
				?>
                  </td>
              </tr>
            </table>
            <table border="0" cellspacing="2" cellpadding="2">
              <tr> 
                <td>Name</td>
                <td> 
                  <?=$name?>
                </td>
              </tr>
              <tr> 
                <td>Address</td>
                <td> 
                  <?=$address_line_1?>
                </td>
              </tr>
              <tr> 
                <td>&nbsp;</td>
                <td> 
                  <?=$address_line_2?>
                </td>
              </tr>
              <tr> 
                <td>&nbsp;</td>
                <td> 
                  <?=$address_line_3?>
                </td>
              </tr>
              <tr> 
                <td>&nbsp;</td>
                <td> 
                  <?=$address_line_4?>
                </td>
              </tr>
              <tr> 
                <td>Postcode</td>
                <td> 
                  <?=$postcode?>
                </td>
              </tr>
              <tr> 
                <td>Telephone</td>
                <td> 
                  <?=$telephone?>
                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </table>
            <table border="0" cellspacing="2" cellpadding="2">
              <tr> 
                <td><b>Title</b></td>
                <td><b>Amount</b></td>
              </tr>
              <?
mysql_connect("localhost", "dramatic", "clo25ck");
mysql_select_db("dramatic_lines");

$qry = mysql_query("select * from titles order by ord");

while ($row = mysql_fetch_array($qry)) {
   $evl = "\$copies = \$copies_".$row["title_id"].";";
   eval($evl);
   if($copies > 0) {
	$price_each    =  $row["price"];
	$p_and_p_each  =  $row["p_and_p"];
	if($submit_non_uk) {
		$p_and_p_each = $p_and_p_each * 1.5;
	}
	
	$price         =  $price_each * $copies;
	$p_and_p       =  $p_and_p_each * $copies;
	$total_price   += $price;
	$total_p_and_p += $p_and_p;

?>
              <tr> 
                <td> 
                  <?=$copies?>
                  x <b> 
                  <?=$row["title"]?>
                  </b> @ &pound; 
                  <? printf("%01.2f", $price_each) ?>
                  + &pound; 
                  <? printf("%01.2f", $p_and_p_each) ?>
                  p&amp;p </td>
                <td align="right">&pound; <? printf("%01.2f", $price); ?></td>
              </tr>
              <? }
              } ?>
              <tr> 
                <td><b>post &amp; packaging</b></td>
                <td align="right">&pound; 
                  <? 
				  printf("%01.2f", $total_p_and_p);?>
                </td>
              </tr>
              <tr>
                <td><b>Total</b></td>
                <td align="right"><b>&pound; 
                  <? printf("%01.2f", $total_price+$total_p_and_p);?>
                  </b></td>
              </tr>
            </table>
			
			<p>&nbsp;</p>
            <table border="0" cellspacing="2" cellpadding="2" width="5">
              <tr>
                <td>
                  <p>Please make sterling cheques payable to <b>Dramatic Lines</b>, 
                    enclose official order or supply credit card details.</p>
                  <p>&nbsp;</p>
                  </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellspacing="2" cellpadding="2" align="center">
                    <tr> 
                      <td nowrap>Visa / mastercard no</td>
                      <td nowrap>___________________________</td>
                    </tr>
                    <tr> 
                      <td nowrap>Expiry Date</td>
                      <td nowrap>____ / ____</td>
                    </tr>
                    <tr> 
                      <td nowrap>Name on card</td>
                      <td nowrap>___________________________</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <p> 
              <?
		  } else {
?>
              To <b>post</b> or <b>fax</b>, please complete the form and press 
              the Create Order Form button </p>
            <form name="form_details" method="post" action="">
              <table border="0" cellspacing="2" cellpadding="2" width="100">
                <tr> 
                  <td>Name</td>
                  <td> 
                    <input type="text" name="name" size="20" maxlength="60">
                  </td>
                </tr>
                <tr> 
                  <td>Address</td>
                  <td> 
                    <input type="text" name="address_line_1" size="30" maxlength="60">
                  </td>
                </tr>
                <tr> 
                  <td>&nbsp;</td>
                  <td> 
                    <input type="text" name="address_line_2" size="30" maxlength="60">
                  </td>
                </tr>
                <tr> 
                  <td>&nbsp;</td>
                  <td> 
                    <input type="text" name="address_line_3" size="30" maxlength="60">
                  </td>
                </tr>
                <tr> 
                  <td>&nbsp;</td>
                  <td> 
                    <input type="text" name="address_line_4" size="30" maxlength="60">
                  </td>
                </tr>
                <tr> 
                  <td>Postcode</td>
                  <td> 
                    <input type="text" name="postcode" size="12" maxlength="12">
                  </td>
                </tr>
                <tr>
                  <td>Telephone</td>
                  <td>
                    <input type="text" name="telephone" size="30" maxlength="30">
                  </td>
                </tr>
              </table>
              <table border="0" cellspacing="2" cellpadding="2">
                <tr> 
                  <td><b>Copies</b></td>
                  <td><b>Title</b></td>
                </tr>
<?
mysql_connect("localhost", "dramatic", "clo25ck");
mysql_select_db("dramatic_lines");

$qry = mysql_query("select * from titles order by ord");

while ($row = mysql_fetch_array($qry)) {
?>

                <tr> 
                  <td> 
                    <input type="text" name="copies_<?=$row["title_id"]?>" size="4" maxlength="6">
                  </td>
                  <td> 
                    <b><?=$row["title"]?></b>
                    @ &pound;<?=$row["price"]?>
                    + &pound;<?=$row["p_and_p"]?> 
					p&amp;p </td>
                </tr>
<? } ?>
              </table>
              <br>
              <table border="0" cellspacing="2" cellpadding="2">
                <tr>
                  <td><b>UK Customers</b></td>
                  <td>
                    <input type="submit" name="submit_uk" value="Create Order Form">
                  </td>
                </tr>
                <tr>
                  <td><b>Non-UK Customers</b></td>
                  <td>
                    <input type="submit" name="submit_non_uk" value="Create Order Form">
                  </td>
                </tr>
              </table>
              </form>
            <p>&nbsp;</p>
            <script language="JavaScript">
            <!--
			window.document.form_details.name.focus()
            //-->
            </script>
			<!>
			<? } ?>
            </td>
		  </tr>
        <tr align="center" valign="bottom"> 
          <td colspan="2"> 
            <p>&nbsp;</p>
            <!-- #BeginLibraryItem "/Library/contact info.lbi" --> 
<p align="center"><span class="small"><b>Dramatic Lines Publishers</b> PO Box 
  201, Twickenham, TW2 5RQ, England<br>
  <b>Tel:</b> +44 (0)20 8296 9502 <b>Fax:</b> +44 (0)20 8296 9503 <b>E-mail:</b> 
  <a href="mailto:mail@dramaticlines.co.uk">mail@dramaticlines.co.uk</a></span></p>
<!-- #EndLibraryItem --></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
