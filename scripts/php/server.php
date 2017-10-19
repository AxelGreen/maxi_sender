<?php

    $current_url = $_SERVER['REQUEST_URI'];

    // index page
    if ($current_url === '/') {
        return false; // miss this file and let to get file from public folder
    }

    // mail.ru postmaster check
    if (preg_match('%/mailru-verification([^\.]+)\.html%', $current_url, $rez)) {

        echo 'verification: '.$rez[1];

    }

?>