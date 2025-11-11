<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Enums\AlertChannels;
use App\Enums\CacheKeys;
use App\Enums\Roles;
use App\Http\Requests\TeamControllerMemberStoreRequest;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TeamController extends Controller
{
    protected $userId;

    protected string $cacheKey;

    public function __construct()
    {
        $this->userId   = Auth::id();
        $this->cacheKey = CacheKeys::MEMBERS->key($this->userId);
    }

    public function index()
    {
        // Verifica se os dados já estão em cache
        if (! Cache::has($this->cacheKey)) {
            $members = Member::with('roles')->get();
            Cache::forever($this->cacheKey, $members);
        }

        //Recupera os dados do cache
        $members = Cache::get($this->cacheKey);

        $roles         = Roles::labels();
        $alertChannels = AlertChannels::labels();

        return Inertia::render('Team/Index', [
            'members'       => $members,
            'roles'         => $roles,
            'alertChannels' => $alertChannels,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Team/Create');
    }

    public function store(TeamControllerMemberStoreRequest $request)
    {
        try {
            $data                   = $request->validated();
            $data['alert_channels'] = json_encode($data['alert_channels']);
            $role                   = Role::where('name', $data['role'])->pluck('id');

            unset($data['role']);

            $member = Member::create($data);

            $member->roles()->attach($role);

            //limpa o cache
            Cache::forget($this->cacheKey);

            return redirect()->back()->with('success', 'Membro adicionado com sucesso');
        } catch (\Exception $e) {
            Log::channel('member')->error($e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(TeamControllerMemberStoreRequest $request, Member $team)
    {
        try {
            $data = $request->validated();

            // Garantir que alert_channels seja sempre um JSON válido
            if (isset($data['alert_channels']) && is_array($data['alert_channels'])) {
                $data['alert_channels'] = json_encode($data['alert_channels']);
            } elseif (isset($data['alert_channels']) && empty($data['alert_channels'])) {
                $data['alert_channels'] = json_encode([]);
            }

            $role = Role::where('name', $data['role'])->pluck('id');

            unset($data['role']);

            $team->update($data);

            // Remove os papéis antigos e atribui os novos
            $team->roles()->sync($role);

            //limpa o cache
            Cache::forget($this->cacheKey);

            return redirect()->back()->with('success', 'Membro atualizado com sucesso');
        } catch (\Exception $e) {
            Log::channel('member')->error($e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Member $team)
    {
        try {
            // Remove todos os relacionamentos
            $team->roles()->detach();

            // Exclui o membro
            $team->delete();

            //limpa o cache
            Cache::forget($this->cacheKey);

            return redirect()->back()->with('success', 'Membro excluído com sucesso');
        } catch (\Exception $e) {
            Log::channel('member')->error($e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
