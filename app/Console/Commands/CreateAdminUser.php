<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Enums\Can;
use App\Enums\Roles;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--name= : Nome do administrador}
                            {--email= : Email do administrador}
                            {--password= : Senha do administrador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um usuário administrador com todas as permissões';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Criando Usuário Administrador ===');
        $this->newLine();

        // Garantir que roles e permissions existam
        $this->ensureRolesAndPermissions();

        // Coletar informações do usuário
        $name = $this->option('name') ?: $this->ask('Nome do administrador', 'Administrador');
        $email = $this->option('email') ?: $this->ask('Email do administrador');
        $password = $this->option('password') ?: $this->secret('Senha do administrador');

        // Validar dados
        $validator = Validator::make([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ], [
            'name'     => ['required', 'string', 'min:3'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Erro na validação:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }

            return Command::FAILURE;
        }

        // Verificar se o email já existe
        if (User::where('email', $email)->exists()) {
            $this->error("O email {$email} já está cadastrado!");

            if (! $this->confirm('Deseja atualizar o usuário existente para administrador?', false)) {
                return Command::FAILURE;
            }

            $user = User::where('email', $email)->first();
            $user->update([
                'name'     => $name,
                'password' => Hash::make($password),
                'role'     => UserRoleEnum::ADMIN->value,
                'status'   => UserStatusEnum::ACTIVE->value,
            ]);
        } else {
            // Criar novo usuário
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => UserRoleEnum::ADMIN->value,
                'status'            => UserStatusEnum::ACTIVE->value,
                'email_verified_at' => now(),
            ]);
        }

        // Atribuir role 'admin' do sistema de roles
        $adminRole = Role::where('name', Roles::Admin->value)->first();
        if ($adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        // Atribuir todas as permissões
        $permissionIds = Permission::pluck('id')->toArray();
        if (! empty($permissionIds)) {
            $user->permissions()->syncWithoutDetaching($permissionIds);
        }

        $this->newLine();
        $this->info('✓ Usuário administrador criado com sucesso!');
        $this->newLine();
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Nome', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Status', $user->status],
                ['Permissões', $user->permissions()->count() . ' permissões'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Garante que roles e permissions existam no banco de dados.
     */
    private function ensureRolesAndPermissions(): void
    {
        // Criar roles se não existirem
        foreach (Roles::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        // Criar permissions se não existirem
        foreach (Can::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }
    }
}

