<?

print "<pre>";
	ini_set('display_startup_errors', 'On');
        ini_set('display_errors', 'On');

        error_reporting (E_ALL ^ E_NOTICE);


include('Mail.php');
include('Mail/mime.php');

    $sFileName='msg3.html';
    $ridFile = fopen($sFileName, "r");
    $html = fread($ridFile, filesize($sFileName));
    fclose($ridFile);


$subj='Центр семейной медицины "Вера Надежда Любовь"';
$subj = '=?windows-1251?B?'.base64_encode($subj).'?=';

//$crlf = "\r\n";
$crlf = "\n";
$hdrs = array(
             'From'    => 'Faith Hope Love <vnl8@mail.ru>',
             'Subject' => $subj

            );

$mime = new Mail_mime($crlf);

$mime->addHTMLImage ('logo.jpg', 'image/jpg');
$mime->addHTMLImage ('img1.jpg', 'image/jpg');
$mime->addHTMLImage ('img3.jpg', 'image/jpg');
$mime->addHTMLImage ('img4.jpg', 'image/jpg');
$mime->addHTMLImage ('img5.jpg', 'image/jpg');

$mime->setHTMLBody($html);

$param = array(
//    'html_encoding' => 'base64',
    'html_charset' => 'windows-1251'
//    'head_charset' => 'windows-1251'
);

$body = $mime->get($param);

$hdrs = $mime->headers($hdrs);

//   $email='4raznoe@gmail.com';
//   $email='dtsygulskiy@aidoss.com';
//   $email='vmaksymenko@aidoss.com';
//   $email='manager@webalta.net.ua';

$hdrs['Return-path']='vnl8@mail.ru';


        $mail_params['sendmail_path'] = '/usr/sbin/sendmail';
        $mail_params['sendmail_args'] = ' -t -i -f '.$hdrs['Return-path'];
        $mail=Mail::factory('sendmail', $mail_params);


$send_one=200;
$from_file='mails.txt';
$emails = file($from_file);

//foreach ($emails as $email)
for ($i=0; $i<$send_one; $i++)
{
$email=$emails[$i];

	$email=trim($email);
	if ($email != '' && checkEmail($email)) {

		print $email;

		$hdrs["To"]=$email;
		$result=$mail->send($email, $hdrs, $body);

		if (PEAR::isError($result)) print " - ".$result->getMessage()."\n";
		else print " - send\n";
	}
}


if (is_writable($from_file)) {

    if (!$handle = fopen($from_file, 'w')) {
         echo "Не могу открыть файл ($from_file)";
         exit;
    }

for ($i=$send_one+1; $i<count($emails); $i++)
{
$email=$emails[$i];

//		print $email;

    // Записываем $somecontent в наш открытый файл.
    if (fwrite($handle, $email) === FALSE) {
        echo "Не могу произвести запись в файл ($from_file)";
        exit;
    }


}

    
    fclose($handle);

} else {
    echo "Файл $from_file недоступен для записи";
}

	exit;


   function checkEmail($sEmail)
   {
    return eregi("^[a-z0-9]+([_.-][a-z0-9]+)*@([a-z0-9]+([.-][a-z0-9]+)*)+\\.[a-z]{2,4}$", $sEmail);
   };


?>