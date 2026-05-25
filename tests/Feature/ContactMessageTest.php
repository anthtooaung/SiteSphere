<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use SweetAlert2\Laravel\Swal;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    public function test_contact_message_can_be_sent(): void
    {
        Config::set('mail.contact.recipient', 'anthtooaung2792005@outlook.com');
        Mail::fake();

        $response = $this->post(route('contact.store'), [
            'first_name' => 'Ant',
            'last_name' => 'Aung',
            'email' => 'ant@example.com',
            'message' => 'Please review my website.',
        ]);

        $response
            ->assertRedirect(route('welcome', ['scroll' => 'contact']))
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['position'] === 'top-end'
                    && $toast['showConfirmButton'] === false
                    && $toast['icon'] === 'success'
                    && $toast['title'] === 'Message sent';
            });

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail): bool {
            return $mail->hasTo('anthtooaung2792005@outlook.com')
                && $mail->hasReplyTo('ant@example.com', 'Ant Aung')
                && $mail->messageData['message'] === 'Please review my website.';
        });
    }

    public function test_contact_message_requires_valid_fields(): void
    {
        Mail::fake();

        $response = $this
            ->from(route('welcome', ['scroll' => 'contact']))
            ->post(route('contact.store'), [
                'first_name' => '',
                'last_name' => '',
                'email' => 'not-an-email',
                'message' => '',
            ]);

        $response
            ->assertRedirect(route('welcome', ['scroll' => 'contact']))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_contact_message_preserves_input_when_mail_delivery_fails(): void
    {
        Config::set('mail.contact.recipient', 'anthtooaung2792005@outlook.com');

        Mail::shouldReceive('to')
            ->once()
            ->with('anthtooaung2792005@outlook.com')
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function (ContactMessageMail $mail): bool {
                return $mail->messageData['email'] === 'ant@example.com';
            })
            ->andThrow(new TransportException('SMTP failed.'));

        $response = $this->post(route('contact.store'), [
            'first_name' => 'Ant',
            'last_name' => 'Aung',
            'email' => 'ant@example.com',
            'message' => 'Please review my website.',
        ]);

        $response
            ->assertRedirect(route('welcome', ['scroll' => 'contact']))
            ->assertSessionHasInput('email', 'ant@example.com')
            ->assertSessionHas(Swal::SESSION_KEY, function (array $toast): bool {
                return $toast['toast'] === true
                    && $toast['icon'] === 'error'
                    && $toast['title'] === 'Message not sent';
            });
    }
}
