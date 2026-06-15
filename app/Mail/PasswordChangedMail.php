<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $userName      Nombre del usuario
     * @param  string  $newPassword   Contraseña nueva (texto plano)
     * @param  string  $loginUrl      URL para iniciar sesión
     */
    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $newPassword,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu contraseña ha sido actualizada — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
            with: [
                'appName' => config('app.name'),
            ],
        );
    }
}
