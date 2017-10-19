<?php

    $current_url = $_SERVER['REQUEST_URI'];

    // index page
    if ($current_url === '/') {
        return false; // miss this file and let to get file from public folder
    }

    // mail.ru postmaster check
    if (preg_match('%/mailru-verification([^\.]{16})\.html%', $current_url, $rez)) {

        echo 'mailru-verification: '.$rez[1];

    }

    // yandex post office check
    if (preg_match('%/([^\.]{64})\.html%', $current_url, $rez)) {

        echo 'postoffice-'.$rez[1];

    }

?>