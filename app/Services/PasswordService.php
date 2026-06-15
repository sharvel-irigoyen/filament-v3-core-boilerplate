<?php

namespace App\Services;

use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Centraliza la lógica de generación de contraseñas
 * y el envío de notificaciones por email.
 */
class PasswordService
{
    /**
     * Genera una contraseña aleatoria segura.
     *
     * Garantiza al menos: 1 mayúscula, 1 minúscula, 1 dígito y 1 símbolo.
     */
    public static function generate(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';  // sin I, O (ambigüedad)
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';    // sin i, l, o
        $digits    = '23456789';                     // sin 0, 1
        $symbols   = '!@#$%&*?';

        // Asegurar al menos un carácter de cada grupo
        $password  = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Rellenar el resto desde el pool completo
        $pool = $uppercase . $lowercase . $digits . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $pool[random_int(0, strlen($pool) - 1)];
        }

        // Mezclar para evitar patrón predecible
        return str_shuffle($password);
    }

    /**
     * Encola email notificando que la contraseña fue cambiada.
     */
    public static function notifyPasswordChanged(User $user, string $plainPassword): void
    {
        Mail::to($user->email)->queue(
            new PasswordChangedMail(
                userName: $user->name,
                userEmail: $user->email,
                newPassword: $plainPassword,
                loginUrl: url('/admin/login'),
            )
        );
    }

    /**
     * Encola email de bienvenida con credenciales.
     */
    public static function notifyWelcome(User $user, string $plainPassword): void
    {
        Mail::to($user->email)->queue(
            new WelcomeMail(
                userName: $user->name,
                userEmail: $user->email,
                password: $plainPassword,
                loginUrl: url('/admin/login'),
            )
        );
    }
}
