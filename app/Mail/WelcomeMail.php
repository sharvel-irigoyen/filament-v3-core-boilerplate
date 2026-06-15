<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $userName   Nombre del usuario
     * @param  string  $userEmail  Email del usuario
     * @param  string  $password   Contraseña auto-generada (texto plano)
     * @param  string  $loginUrl   URL para iniciar sesión
     */
    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $password,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a ' . config('app.name') . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'appName' => config('app.name'),
            ],
        );
    }
}
