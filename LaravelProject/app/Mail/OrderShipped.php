<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Headers;
use Symfony\Component\Mime\Email;
use App\Models\Order;
use App\Models\Photo;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    // 公開屬性，Blade 模板可直接取得
    public Order $order;
    // Attachable 物件
    public Photo $photo;
    // 內嵌圖片路徑
    public string $inlineImagePath;
    // 原始 PDF 資料
    public string $pdfData;

    /**
     * 建構子，注入訂單、照片、圖片路徑、PDF 資料
     */
    public function __construct(Order $order, Photo $photo, string $inlineImagePath, string $pdfData)
    {
        $this->order = $order;
        $this->photo = $photo;
        $this->inlineImagePath = $inlineImagePath;
        $this->pdfData = $pdfData;
    }

    /**
     * 設定信件 Envelope（寄件人、回覆、主旨、標籤、元資料、自訂 Symfony Message）
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('jeffrey@example.com', 'Jeffrey Way'), // 寄件人
            replyTo: [
                new Address('taylor@example.com', 'Taylor Otwell'), // 回覆地址
            ],
            subject: '訂單已出貨', // 主旨
            tags: ['shipment'], // 標籤
            metadata: [
                'order_id' => $this->order->id, // 元資料
            ],
            using: [
                function (Email $message) {
                    // 自訂 Symfony Message，例如設定優先權
                    $message->priority(1);
                },
            ]
        );
    }

    /**
     * 設定信件內容（HTML、純文字模板、with 傳遞資料）
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.shipped', // HTML 模板
            text: 'mail.orders.shipped-text', // 純文字模板
            with: [
                // 這裡可自訂傳遞給模板的變數
                'orderName' => $this->order->name,
                'orderPrice' => $this->order->price,
                'inlineImagePath' => $this->inlineImagePath,
            ],
        );
    }

    /**
     * 設定附件（本地檔案、Storage、原始資料、Attachable 物件）
     */
    public function attachments(): array
    {
        return [
            // 本地檔案附件
            Attachment::fromPath(storage_path('app/invoice.pdf'))
                ->as('發票.pdf')
                ->withMime('application/pdf'),
            // Storage 檔案附件
            Attachment::fromStorage('orders/' . $this->order->id . '/receipt.pdf')
                ->as('收據.pdf')
                ->withMime('application/pdf'),
            // 原始資料附件（如 PDF）
            Attachment::fromData(fn () => $this->pdfData, '報表.pdf')
                ->withMime('application/pdf'),
            // Attachable 物件（如照片）
            $this->photo,
        ];
    }

    /**
     * 設定自訂郵件標頭
     */
    public function headers(): Headers
    {
        return new Headers(
            messageId: 'order-' . $this->order->id . '@example.com',
            references: ['previous-message@example.com'],
            text: [
                'X-Order-Header' => 'Order-' . $this->order->id,
            ],
        );
    }
}
