<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $curl = curl_init();
    curl_setopt(
        $curl,
        CURLOPT_URL,
        'https://www.google.com/recaptcha/api/siteverify'
    );
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
        $curl,
        CURLOPT_POSTFIELDS,
        array(
            'secret' => '6LfAqQoUAAAAAG8uwud3o_4CBxITWT2qSYVM8ZyF',
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    );

    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    $response = json_decode(trim(curl_exec($curl)), true);
    if (!empty($response['success']) and $response['success'] == 'true') {
        require_once __DIR__ . '/BadBotBlocker.php';
        require_once __DIR__ . '/config-test.php';
        $badBotObj = new BadBotBlocker(true);
        $badBotObj->enableAccess();
        if (!empty($_GET['url'])) {
            header(
                "Location: " . base64_decode(filter_input(INPUT_GET, 'url')),
                true,
                301
            );
        } else {
            header("Location: index.php", true, 301);
        }
        exit;
    }
}
$url = filter_input(INPUT_GET, 'url');
$key = '6LfAqQoUAAAAAJClb7DM8o6C2jTfvr8k9cpDxn4H';
?>
<html>
    <head>
        <title>Captcha validation</title>
    </head>
    <body>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <form action="captcha.php?url=<?php echo $url; ?>" method="post">
            <div class="g-recaptcha" data-sitekey="<?php echo $key; ?>"></div>
            <br />
            <button type="submit">Validate</button>
        </form>
    </body>
</html>