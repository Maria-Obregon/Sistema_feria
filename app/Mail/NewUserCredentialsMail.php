<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $passwordPlano,
        public bool $esReset = false
    ) {}

    public function build()
    {
        return $this->subject($this->esReset ? 'Contraseña restablecida' : 'Tu acceso a la plataforma')
            ->markdown('emails.new-user-credentials'); // << Markdown en vez de ->view()
    }
}
