<?php

require_once 'vendor/autoload.php';
use Mailgun\Mailgun;

Class Notification {
    // Email settings
    private string $subject;
    private string $body;
    private string $email;
    private string $email_from = 'postmaster@sandbox250009e5b0474789aef35e0389edc984.mailgun.org';

    // Mailgun settings
    private string $mailgun_api_key = 'a26b1841-4edd53a5';
    private string $mailgun_public_api_key = 'pubkey-6b90e40f9f0e30c8afd14164d5fb558f';
    private string $mailgun_domain = 'sandbox250009e5b0474789aef35e0389edc984.mailgun.org';
    // For outside EU remove .eu below:
    private string $mailgun_endpoint = 'https://api.eu.mailgun.net';
    private string $mailgun_smtp_user = '022d582ddea5c4f0bce7beb9edf29cba-a26b1841-a8e0afcb';
    private string $mailgun_smtp_password = 'postmaster@sandbox250009e5b0474789aef35e0389edc984.mailgun.org';

    // DB settings
    private string $dbtable = 'notifications';
    private $conn;

    public function __construct() {
        $dbhost = 'db';
        $dbuser = 'user';
        $dbpassword = 'user';
        $dbname = 'default';
        $this->conn = mysqli_connect($dbhost, $dbuser, $dbpassword)
        or die('Error: DB connection does not exist'.PHP_EOL . mysqli_error($this->conn).PHP_EOL);
        mysqli_select_db($this->conn, $dbname);
        mysqli_query($this->conn, 'set names utf8');
        mysqli_query($this->conn, 'set time_zone="Europe/Sofia"');
    }

    public function create($email, $subject, $body): bool {
        if (empty($email) || empty($subject) || empty($body)
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !filter_var($this->email_from, FILTER_VALIDATE_EMAIL)
        ) return FALSE;
        $this->email = $email;
        $this->subject = $subject;
        $this->body = $body;
        return TRUE;
    }

    public function save(): bool {
        $this->email = mysqli_real_escape_string($this->conn, $this->email);
        $this->subject = mysqli_real_escape_string($this->conn, $this->subject);
        $this->body = mysqli_real_escape_string($this->conn, $this->body);
        $res = mysqli_query($this->conn, 'insert into `'.$this->dbtable.'` (`email`, `subject`, `body`) '
            .' values ("'.$this->email.'", "'.$this->subject.'", "'.$this->body.'")');
        if (empty($res)) return FLASE;
        return TRUE;
    }

    private function send_mail(): bool {
        // Simple PHP mail() integration.
        // Uncomment the following lines to use PHP mail()...
        /*if (@mail($this->email, $this->subject, $this->body, $this->mail_headers()))
            return TRUE;
        else return FALSE;
        */
        // Mailgun integration.
        try {
            $mg = Mailgun::create($this->mailgun_public_api_key, $this->mailgun_endpoint, $this->mailgun_api_key);
            $res = $mg->messages()->send($this->mailgun_domain, [
                'from' => $this->email_from,
                //'au' => $this->mailgun_smtp_user,
                //'ap' => $this->mailgun_smtp_password,
                'to' => $this->email,
                'subject' => $this->subject,
                'text' => $this->body
            ])->getStatusCode();
            return $res === 200;
        } catch (Exception $e) {
            die('Mailgun Error: ' . $e->getMessage() . PHP_EOL);
            //return FALSE;
        }
        //return TRUE;
    }

    private function mail_headers(): string {
        $sys_email = $this->email_from;
        $email_to = $this->email;
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        return "MIME-Version: 1.0\r\n"
            . "Content-type: text/plain; charset=utf-8\r\n"
            . "From: <{$sys_email}>\r\n"
            . "Reply-To: <{$sys_email}>\r\n"
            . "Return-Path: <{$sys_email}>\r\n"
            . "Date: " . date("r")."\r\n"
            . "Message-ID: <".time()."-".$email_to.">\r\n"
            . "X-Originating-IP: [".$ip."]\r\n"
            . "X-Mailer: PHP/" . phpversion();
    }

    public function exec($step): bool {
        $ok = TRUE;
        $query = mysqli_query($this->conn, 'select * from `'.$this->dbtable.'` where `status`="0"  limit '.$step);
        while ($notif = mysqli_fetch_assoc($query)) {
            if ($ok && !$this->create($notif['email'], $notif['subject'], $notif['body'])) $ok = FALSE;
            if ($ok && $this->send_mail()) {
                $update = mysqli_query($this->conn,
                    'update `'.$this->dbtable.'` set `status`="1", `updated`=NOW() where `id`="'.$notif['id'].'"');
                $res = !empty($update);
            } else $ok = FALSE;
            if (!$ok) break;
        }
        return $ok;
    }

    public function list(): array {
        $res = [];
        $query = mysqli_query($this->conn, 'select * from `'.$this->dbtable.'`');
        while ($row = mysqli_fetch_row($query)) $res[] = $row;
        return $res;
    }

}
