<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Onboarding;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Address\AddressStore;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingStore extends Controller
{
    use SetLogTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(UserStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = Auth::user();

            //faz o upload do rg_cnh_frente e rg_cnh_verso e selfie
            $rg_cnh_frente = $request->file('rg_cnh_frente');
            $rg_cnh_verso  = $request->file('rg_cnh_verso');
            $selfie        = $request->file('selfie');

            //salva o arquivo no storage
            $rg_cnh_frente = $rg_cnh_frente->store('rg_cnh_frente', 'public');
            $rg_cnh_verso  = $rg_cnh_verso->store('rg_cnh_verso', 'public');
            $selfie        = $selfie->store('selfie', 'public');

            $user->update([
                'person_type'     => $request->validated('person_type'),
                'full_name'       => $request->validated('full_name'),
                'phone'           => $request->validated('phone'),
                'document'        => $request->validated('document'),
                'average_revenue' => $request->validated('average_revenue'),
                'average_ticket'  => $request->validated('average_ticket'),
                'products'        => $request->validated('products'),
                'social_reason'   => $request->validated('social_reason'),
                'status'          => UserStatusEnum::PENDING->value,
                'social_contract' => $request->validated('social_contract'),
                'rg_cnh_frente'   => $rg_cnh_frente,
                'rg_cnh_verso'    => $rg_cnh_verso,
                'selfie'          => $selfie,
            ]);

            (new AddressStore(
                zip_code: $request->validated('zip_code'),
                address: $request->validated('address'),
                number: $request->validated('number'),
                city: $request->validated('city'),
                state: $request->validated('state'),
            ))();

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Usuário atualizado com sucesso');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setLog(channel: 'user', message: 'Erro ao criar usuário: ' . $e->getMessage(), type: 'error');
        }

        return null;
    }
}
