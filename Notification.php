<?php

Class Notification
{
    private $email_from = 'no_reply@domain.com';
    private $subject;
    private $body;
    private $email;
    private $conn;
    private $dbtable;

    public function __construct() {
        $host = 'db';
        $user = 'user';
        $psw = 'user';
        $db = 'default';
        $table = 'notifications';
        //$port = '3306';
        //$socket = NULL;
        $conn = mysqli_connect($host, $user, $psw, $db)
        or die('DB connection does not exist'.PHP_EOL . mysqli_error($conn).PHP_EOL);

        mysqli_query($conn, 'set names utf8');
        mysqli_query($conn, 'set time_zone="Europe/Sofia"');

        $this->conn = $conn;
        $this->dbtable = $table;
    }

    public function create($email, $subject, $body) {
        if (empty($email) || empty($subject) || empty($body)
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !filter_var($this->email_from, FILTER_VALIDATE_EMAIL)
        ) return FALSE;
        $this->email = $email;
        $this->subject = $subject;
        $this->body = $body;
        return TRUE;
    }

    public function save() {
        $this->email = mysqli_real_escape_string($this->conn, $this->email);
        $this->subject = mysqli_real_escape_string($this->conn, $this->subject);
        $this->body = mysqli_real_escape_string($this->conn, $this->body);
        return mysqli_query($this->conn, 'insert into `'.$this->dbtable.'` (`email`, `subject`, `body`) '
            .' values ("'.$this->email.'", "'.$this->subject.'", "'.$this->body.'")');
    }

    private function send_mail(): bool {
        if (@mail($this->email, $this->subject, $this->body, $this->mail_headers()))
            return TRUE;
        else
            return FALSE;
    }

    private function mail_headers(): string {
        $sys_email = $this->email_from;
        $email_to = $this->email;
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'));
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
        $query = mysqli_query($this->conn,
            'select * from `'.$this->dbtable.'` where `status`="0"  limit '.$step);
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

}
