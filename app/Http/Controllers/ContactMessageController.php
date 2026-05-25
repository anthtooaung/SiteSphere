<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use SweetAlert2\Laravel\Swal;
use Throwable;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $messageData = $request->validated();
        $recipient = (string) config('mail.from.address');

        try {
            Mail::to($recipient)->send(new ContactMessageMail($messageData));
        } catch (Throwable $exception) {
            report($exception);
            $this->flashToast(
                icon: 'error',
                title: 'Message not sent',
                text: 'We could not send your message right now. Please try again shortly.',
            );

            return redirect()
                ->route('welcome', ['scroll' => 'contact'])
                ->withInput();
        }

        $this->flashToast(
            icon: 'success',
            title: 'Message sent',
            text: 'Thanks for reaching out. We will get back to you shortly.',
        );

        return redirect()->route('welcome', ['scroll' => 'contact']);
    }

    private function flashToast(string $icon, string $title, string $text): void
    {
        Swal::fire([
            'toast' => true,
            'position' => 'top-end',
            'showConfirmButton' => false,
            'timer' => 3000,
            'timerProgressBar' => true,
            'icon' => $icon,
            'title' => $title,
            'text' => $text,
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);
    }
}
