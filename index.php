<?php
// Web interface.

// Show all errors.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Notification.php';
$notif = new Notification();

$html = <<<EOF
<!DOCTYPE>
<html lang="en">
<head>
    <title>{title}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{margin:50px auto;text-align:center;}
    input,textarea{width:350px;max-width:100%;}
    table{border-spacing:0;} tr{margin:0;padding:0;}
    th,td{border: 1px solid #dedede;margin:0;padding:2px 10px;text-align:center;}</style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <h1>{title}</h1>
    {body}
    <br><br>
    {table}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
EOF;

if (!empty($_POST['email']) && !empty($_POST['subject']) && !empty($_POST['text'])) {
    if (!$notif->create($_POST['email'], $_POST['subject'], $_POST['text'])) {
        $title = 'Error';
        $body = '<p>Error: The parameters are not valid</p>p>';
    } else {
        if (!$notif->save()) {
            $title = 'Error';
            $body = '<p>Error: The notification is not saved</p>';
        } else {
            $title = 'Success';
            $body = '<p>The notification is created successfully</p>';
        }
    }
} else {
    $title = 'Notification Form';
    $body = <<<EOT
    <form method="post" action="index.php">
        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" required>
        <br><br>
        <label for="subject">Subject:</label><br>
        <input type="text" name="subject" id="subject" required>
        <br><br>
        <label for="text">Body text:</label><br>
        <textarea name="text" rows="3" id="text" required></textarea>
        <br><br>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
EOT;
}

// DB records.
$table = '<table class="table"><tr><th>id</th><th>email</th><th>subject</th><th>body</th><th>status</th><th>created</th></th><th>updated</th></tr>';
$list = $notif->list();
if (empty($list)) $table .= '<tr><td colspan="7">The database is empty</td></tr>';
else {
    foreach ($list as list($id, $email, $subject, $text, $status, $created, $updated))
        $table .= "<tr><td>$id</td><td>$email</td><td>$subject</td><td>$text</td><td>"
            .(!empty($status) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-square text-danger"></i>')
            ."</th><th>$created</th></th><th>$updated</th></tr>";
}
$table .= '</table>';

// Show the page.
$html = str_replace('{title}', $title, $html);
$html = str_replace('{body}', $body, $html);
$html = str_replace('{table}', $table, $html);

echo $html;
