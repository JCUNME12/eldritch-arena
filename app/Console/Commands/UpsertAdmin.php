<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UpsertAdmin extends Command
{
    protected $signature = 'app:admin
        {email : E-mail da conta administrativa}
        {--name=Administrador : Nome exibido da conta}
        {--password-env=ELDRITCH_ADMIN_PASSWORD : Variável de ambiente que contém a senha inicial}';

    protected $description = 'Cria ou promove uma conta administrativa sem armazenar a senha no código';

    public function handle(): int
    {
        $passwordVariable = (string) $this->option('password-env');
        $password = getenv($passwordVariable);

        if (! is_string($password) || $password === '') {
            if (! $this->input->isInteractive()) {
                $this->error("Defina a variável {$passwordVariable} ou execute o comando interativamente.");

                return self::FAILURE;
            }

            $password = (string) $this->secret('Senha inicial (mínimo de 12 caracteres)');
        }

        $data = [
            'email' => mb_strtolower((string) $this->argument('email')),
            'name' => trim((string) $this->option('name')),
            'password' => $password,
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'type' => 'organizer',
                'is_admin' => true,
                'avatar_color' => '#FBBF24',
                'email_verified_at' => now(),
            ]
        );

        $this->info("Administrador {$user->email} configurado com sucesso.");

        return self::SUCCESS;
    }
}
