<?php

require_once __DIR__ . '/BadBotBlocker.php';
require_once __DIR__ . '/config-test.php';

$badBotObj = new BadBotBlocker\Blocker(true);
$badBotResponse = $badBotObj->checkAccess();
if ($badBotResponse == 'captcha') {
    header(
        "Location: captcha.php?url="
        . base64_encode(
            $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']
        ),
        true,
        301
    );
    exit;
}

echo $badBotResponse;
