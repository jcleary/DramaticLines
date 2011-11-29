
<?php

// @todo remove once we're on 5.3
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (SYSTEM_TYPE == SYSTEM_LIVE) {
    set_error_handler("errorHandlerLive");
} else {
    set_error_handler("errorHandlerDevTest");
}

// ------------------------------------------------------------------
function errorHandlerDevTest($ErrNo, $ErrMsg, $Filename, $LineNum, $Vars)
// ------------------------------------------------------------------

{	
    if (in_array($ErrNo, array(E_NOTICE, E_DEPRECATED))) {
         // we aren't interested in backtrace for notice errors
        // so just:
        return;
    }

    $ErrorType = array (
        E_ERROR           => "Error",
        E_WARNING         => "Warning",
        E_PARSE           => "Parsing Error",
        E_NOTICE          => "Notice",
        E_CORE_ERROR      => "Core Error",
        E_CORE_WARNING    => "Core Warning",
        E_COMPILE_ERROR   => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR      => "User Error",
        E_USER_WARNING    => "User Warning",
        E_USER_NOTICE     => "User Notice",
        E_DEPRECATED      => "Deprecated",
    );

    $UserErrors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

    $Err = '<table class="error" border=1>';
    $Err .= "<tr><th>Type</th><td>" .     $ErrorType[$ErrNo] . " (" . $ErrNo . ")</td></tr>\n";
    $Err .= "<tr><th>Message</th><td>" .  $ErrMsg . "</td></tr>\n";
    $Err .= "<tr><th>Filename</th><td>" . basename($Filename) . "</td></tr>\n";
    $Err .= "<tr><th>Line</th><td>" .     $LineNum . "</td></tr>\n";

    $Err .= "</table>";

    $Err .= backtraceDump(array("errorhandlerdevtest")) . "<br><br>";

    echo $Err . "<br><br>";

    die();
}

// ------------------------------------------------------------------
function errorHandlerLive($ErrNo, $ErrMsg, $Filename, $LineNum, $Vars)
// ------------------------------------------------------------------
{
    if (in_array($ErrNo, array(E_NOTICE, E_DEPRECATED))) {
	         // we aren't interested in backtrace for notice errors
        // so just:
        return;
    }

    $ErrorType = array (
        E_ERROR           => "Error",
        E_WARNING         => "Warning",
        E_PARSE           => "Parsing Error",
        E_NOTICE          => "Notice",
        E_CORE_ERROR      => "Core Error",
        E_CORE_WARNING    => "Core Warning",
        E_COMPILE_ERROR   => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR      => "User Error",
        E_USER_WARNING    => "User Warning",
        E_USER_NOTICE     => "User Notice",
        E_DEPRECATED      => "Deprecated",
        );

    $UserErrors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

    $Err = "";
    $Err .= "<tr><th>Type</th><td>" .     $ErrorType[$ErrNo] . " (" . $ErrNo . ")</td></tr>\n";
    $Err .= "<tr><th>Message</th><td>" .  $ErrMsg . "</td></tr>\n";
    $Err .= "<tr><th>Filename</th><td>" . basename($Filename) . "</td></tr>\n";
    $Err .= "<tr><th>Line</td><th>" .     $LineNum . "</td></tr>\n";

    $Err = "<table border=1>" . $Err . "</table>";

    $Err .= backtraceDump(array("errorhandlerlive"));

    echo "An internal error has occured - the site administrator has been notified<br><br>";

    echo '<a href="' . INDEX_URL . '">Go back to the main page</a>';

    mail(EMAIL_ADMIN, 'Dramatic lines error', $Err, "Content-type: text/html");

    die();
}
