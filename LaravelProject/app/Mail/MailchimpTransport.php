<?php

namespace App\Mail;

use MailchimpTransactional\ApiClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Mailchimp Transactional 自訂郵件傳送驅動
 */
class MailchimpTransport extends AbstractTransport
{
    /**
     * 建構子，注入 Mailchimp API client
     */
    public function __construct(
        protected ApiClient $client,
    ) {
        parent::__construct();
    }

    /**
     * 實作郵件實際發送邏輯
     *
     * @param SentMessage $message
     */
    protected function doSend(SentMessage $message): void
    {
        // 將 Symfony 郵件物件轉為 Email 實例
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        // 呼叫 Mailchimp API 寄送郵件
        $this->client->messages->send(['message' => [
            'from_email' => $email->getFrom(),
            'to' => collect($email->getTo())->map(function (Address $email) {
                return ['email' => $email->getAddress(), 'type' => 'to'];
            })->all(),
            'subject' => $email->getSubject(),
            'text' => $email->getTextBody(),
        ]]);
    }

    /**
     * 傳回 transport 字串識別
     */
    public function __toString(): string
    {
        return 'mailchimp';
    }
} 