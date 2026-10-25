<?php

namespace App\Console\Commands;

use App\Services\SupabaseAdmin;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {name} {role=management}';
    protected $description = 'Buat akun pegawai (role: management, super_admin, admin)';

    public function handle(SupabaseAdmin $db): int
    {
        $role = $this->argument('role');
        if (! in_array($role, ['management', 'super_admin', 'admin'], true)) {
            $this->error('Role harus: management, super_admin, atau admin.');

            return 1;
        }

        $pw = (string) $this->secret('Password (minimal 8 karakter)');
        if (strlen($pw) < 8) {
            $this->error('Password terlalu pendek.');

            return 1;
        }

        $res = $db->createAccount($this->argument('email'), $this->argument('name'), $pw, $role);

        if ($res['ok']) {
            $this->info("Akun dibuat. Silakan login di /admin/login sebagai {$role}.");

            return 0;
        }

        $this->error($res['error']);

        return 1;
    }
}
