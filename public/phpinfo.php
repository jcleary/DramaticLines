<?php

if ($_ENV["HTTP_X_FORWARDED_FOR"] != '213.106.3.2') {
    die();
}

phpinfo();

?>