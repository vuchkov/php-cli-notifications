<?php
// CLI (console) interface.

// Show all errors.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) !== 'cli'){
    header('Location: index.php');
    exit;
}

if (($argv[0] !== $_SERVER['PHP_SELF']) || (count($argv) < 2))
    die('Error: Missing or invalid parameters'.PHP_EOL);

$action = $argv[1] ?? '';
if (empty($action) || (!in_array($action, ['push', 'exec'])))
    die('Error: Invalid parameter `action` (push|exec)'.PHP_EOL);

require_once 'Notification.php'; // Load class Notification.
$notif = new Notification();

if ($action === 'push') {
    $email = $argv[2] ?? '';
    $subject = $argv[3] ?? '';
    $text = $argv[4] ?? '';

    if (!$notif->create($email, $subject, $text)) die('Error: The parameters are not valid'.PHP_EOL
        .'Requires: php notif.php push <email> <TestSubject> <TestBody>'.PHP_EOL);
    if (!$notif->save()) die('Error: The notification is not saved'.PHP_EOL);
    die('The notification is created successfully'.PHP_EOL);
}

if ($action === 'exec') {
    $step = !empty($argv[2]) ? ((int)$argv[2]) : 1;
    if (empty($step)) die('Error: The `step` parameter is not valid'.PHP_EOL);

    if (!$notif->exec($step)) die('Error: The notification is not executed'.PHP_EOL);
    else die('The notification is executed successfully'.PHP_EOL);
}
