<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $createListings = Permission::findOrCreate('create listings', 'web');
        $sendMessages = Permission::findOrCreate('send messages', 'web');

        $anunciante = Role::findOrCreate('anunciante', 'web');
        $anunciante->syncPermissions([$createListings, $sendMessages]);

        $usuario = Role::findOrCreate('usuario', 'web');
        $usuario->syncPermissions([$sendMessages]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Backfill: todo usuário que já existia antes desta migration vira
        // "usuario" por padrão (inclusive admins, como rede de segurança —
        // is_admin continua sendo a fonte de verdade pra acesso ao painel).
        // Em banco novo (testes/instalação local) não há usuários ainda
        // nesse ponto, então isto é um no-op.
        DB::table('users')->select('id')->orderBy('id')
            ->chunkById(200, function ($users) use ($usuario) {
                DB::table('model_has_roles')->insertOrIgnore(
                    $users->map(fn ($user) => [
                        'role_id' => $usuario->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        DB::table('model_has_roles')->where('model_type', User::class)->delete();
        Permission::whereIn('name', ['create listings', 'send messages'])->delete();
        Role::whereIn('name', ['anunciante', 'usuario'])->delete();
    }
};
