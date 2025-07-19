<?php
namespace App\Services;

class Mailer
{
    public function send($to, $content)
    {
        // 實際應用會寄信，這裡僅示範
        return "寄送信件到 $to，內容：$content";
    }
} 