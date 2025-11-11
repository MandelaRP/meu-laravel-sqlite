<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class GroupController extends Controller
{
    protected $userId;

    protected string $cacheKey;

    public function __construct()
    {
        $this->userId   = Auth::id();
        $this->cacheKey = CacheKeys::GROUPS->key($this->userId);
    }

    public function index()
    {
        // Verifica se os dados já estão em cache
        if (! Cache::has($this->cacheKey)) {
            $groups = Group::all();
            Cache::forever($this->cacheKey, $groups);
        }

        // Recupera os dados do cache
        $groups = Cache::get($this->cacheKey);

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();

        // Valida os dados
        $validated = $request->validate([
            'name'        => 'required|string|max:30|unique:groups,name,NULL,id,user_id,' . $userId,
            'description' => 'nullable|string|max:50',
        ], [
            'name.required' => 'O nome do grupo é obrigatório.',
            'name.string'   => 'O nome do grupo deve ser uma string.',
            'name.max'      => 'O nome do grupo deve ter no máximo 30 caracteres.',
            'name.unique'   => 'Já existe um grupo com este nome.',
        ]);

        $validated['user_id'] = $userId;

        // Cria o grupo
        Group::create($validated);

        // Limpa o cache
        Cache::forget($this->cacheKey);
    }

    public function update(Request $request, Group $group): void
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:30|unique:groups,name,' . $group->id . ',id,user_id,' . $this->userId,
            'description' => 'nullable|string|max:50',
        ], [
            'name.required' => 'O nome do grupo é obrigatório.',
            'name.string'   => 'O nome do grupo deve ser uma string.',
            'name.max'      => 'O nome do grupo deve ter no máximo 30 caracteres.',
            'name.unique'   => 'Já existe um grupo com este nome.',
        ]);

        // Atualiza o grupo
        $group->update($validated);

        // Limpa o cache
        Cache::forget($this->cacheKey);
    }

    public function destroy(Group $group): void
    {
        $group->delete();
        Cache::forget($this->cacheKey);
    }
}
