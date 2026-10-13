<?php
class Mailer {
    static function send($to, $subject, $body) {
        $log = "[" . date('c') . "] PARA: $to | ASUNTO: $subject\n$body\n" . str_repeat('-', 40) . "\n";
        @file_put_contents(ROOT . '/storage/mail.log', $log, FILE_APPEND);
        $m = cfg('mail');
        if (!empty($m['enabled'])) {
            @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body,
                "From: {$m['from']}\r\nContent-Type: text/plain; charset=UTF-8");
        }
    }
}
