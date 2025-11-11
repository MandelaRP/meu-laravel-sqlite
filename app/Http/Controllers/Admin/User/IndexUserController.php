<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Filtro por tipo/role - incluir admin12@gmail.com mesmo se tiver is_sample = true
        // Excluir usuários recent_user (que não finalizaram o onboarding)
        $query = User::where(function ($q) {
            $q->where('is_sample', false)
              ->orWhere('email', 'admin12@gmail.com');
        })
        ->where('status', '!=', 'recent_user');
        
        // Filtrar por role se fornecido
        if ($request->has('role') && $request->role && $request->role !== 'all') {
            $query->where('role', $request->role);
        } else {
            // Por padrão, excluir admins
            $query->where('role', '!=', 'admin');
        }
        
        // Busca por nome ou email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Buscar usuários com paginação
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($user) {
                // Carregar relacionamentos para evitar lazy loading
                $user->loadMissing(['acquirer']);
                return $user;
            });
        
        $pendingUsers = User::where('status', 'pending')
            ->where('role', '!=', 'admin')
            ->where(function ($q) {
                $q->where('is_sample', false)
                  ->orWhere('email', 'admin12@gmail.com');
            })
            ->count();

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'pendingUsers' => $pendingUsers,
            'filters' => [
                'role' => $request->role ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }
}
