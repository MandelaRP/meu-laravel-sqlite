<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Acquirers;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateAcquirerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $acquirer = Acquirer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'credentials' => 'sometimes|array',
            'settings' => 'sometimes|array',
            'fixed_fee' => 'sometimes|numeric|min:0',
            'percentage_fee' => 'sometimes|numeric|min:0|max:100',
            'withdrawal_fee' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->has('credentials')) {
            $acquirer->credentials = $request->input('credentials');
        }

        if ($request->has('settings')) {
            $acquirer->settings = $request->input('settings');
        }

        // Atualizar taxas
        if ($request->has('fixed_fee')) {
            $acquirer->fixed_fee = (float) $request->input('fixed_fee');
        }

        if ($request->has('percentage_fee')) {
            $acquirer->percentage_fee = (float) $request->input('percentage_fee');
        }

        if ($request->has('withdrawal_fee')) {
            $acquirer->withdrawal_fee = (float) $request->input('withdrawal_fee');
        }

        $acquirer->save();

        // Recarregar a adquirente para retornar dados atualizados
        $acquirer->refresh();

        return redirect()->back()
            ->with('success', 'Configurações da adquirente atualizadas com sucesso.')
            ->with('acquirers', Acquirer::orderBy('name')->get());
    }
}
