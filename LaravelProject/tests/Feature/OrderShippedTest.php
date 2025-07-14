<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\OrderShipped;
use App\Models\Order;
use App\Models\User;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShippedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function mailable_content_and_structure_are_correct()
    {
        $user = User::factory()->create(['email' => 'taylor@example.com']);
        $order = Order::factory()->create();
        $mailable = new OrderShipped($order);

        // 斷言主旨、收件人、HTML 內容
        $mailable->assertHasSubject('訂單已出貨');
        // 假設 mailable envelope 有設定 to
        // $mailable->assertTo('taylor@example.com');
        $mailable->assertSeeInHtml('訂單已出貨');
        $mailable->assertSeeInOrderInHtml(['訂單已出貨', '感謝您的支持']);
        // 斷言附件（如有）
        // $mailable->assertHasAttachment(Attachment::fromPath('/path/to/file'));
    }

    /** @test */
    public function mail_is_sent_and_queued_correctly()
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'taylor@example.com']);
        $order = Order::factory()->create();

        // 執行寄信
        Mail::to($user)->send(new OrderShipped($order));
        Mail::assertSent(OrderShipped::class, function (OrderShipped $mail) use ($user) {
            // 斷言收件人與主旨
            return $mail->hasTo($user->email) && $mail->hasSubject('訂單已出貨');
        });
        // 斷言未寄送其他 mailable
        Mail::assertNotSent('App\\Mail\\AnotherMailable');

        // 佇列寄信
        Mail::to($user)->queue(new OrderShipped($order));
        Mail::assertQueued(OrderShipped::class);
    }

    /** @test */
    public function mail_is_not_sent_when_not_triggered()
    {
        Mail::fake();
        // 未執行寄信
        Mail::assertNothingSent();
        Mail::assertNothingOutgoing();
    }
} 