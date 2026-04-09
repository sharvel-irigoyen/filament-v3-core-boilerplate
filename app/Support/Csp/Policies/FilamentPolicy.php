<?php

namespace App\Support\Csp\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic;

class FilamentPolicy extends Basic
{
    public function configure()
    {
        $this
            ->addDirective(Directive::BASE, 'self')
            ->addDirective(Directive::CONNECT, 'self')
            ->addDirective(Directive::DEFAULT, 'self')
            ->addDirective(Directive::FORM_ACTION, 'self')
            ->addDirective(Directive::FRAME_ANCESTORS, 'self') // Prevent Clickjacking
            ->addDirective(Directive::IMG, [
                'self',
                'data:',
                'blob:',
                'https://ui-avatars.com',
            ])
            ->addDirective(Directive::MEDIA, 'self')
            ->addDirective(Directive::OBJECT, 'none')
            ->addDirective('worker-src', ['self', 'blob:']) // Allow web workers from blobs (FilePond, etc)
            ->addDirective(Directive::SCRIPT, [
                'self',
                'unsafe-eval', // Required for Alpine.js
                'unsafe-inline', // Temporary fix for Livewire/Filament inline scripts
            ])
            ->addNonceForDirective(Directive::SCRIPT)
            ->addDirective(Directive::STYLE, [
                'self',
                'unsafe-inline', // Filament uses extensive inline styles
                'https://fonts.bunny.net',
            ])
            ->addDirective(Directive::FONT, [
                'self',
                'https://fonts.bunny.net',
                'data:',
            ]);

    }
}
