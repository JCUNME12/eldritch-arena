<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetAdmin extends Command
{
    protected $signature = 'arena:admin {email} {--revoke : Remove o acesso administrativo}';

    protected $description = 'Concede ou revoga acesso administrativo de uma conta existente, com registro de auditoria.';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $this->error('Conta não encontrada. Cadastre a conta antes de conceder acesso.');

            return self::FAILURE;
        }
        $granted = ! $this->option('revoke');
        DB::transaction(function () use ($user, $granted) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($locked->isAdmin() === $granted) {
                return;
            }
            $locked->is_admin = $granted;
            $locked->save();
            DB::table('admin_access_logs')->insert(['user_id' => $locked->id, 'granted' => $granted, 'source' => 'console', 'created_at' => now(), 'updated_at' => now()]);
        });
        $this->info($granted ? 'Acesso administrativo concedido. Senha e perfil preservados.' : 'Acesso administrativo revogado.');

        return self::SUCCESS;
    }
}
