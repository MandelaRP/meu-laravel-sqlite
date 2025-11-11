<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Categories\StoreCategoryRequest;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    use SetLogTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCategoryRequest $request, User $user): RedirectResponse
    {
        try {
            $user->categories()->create($request->validated());

            return redirect()->back()->with('success', 'Categoria criada com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao criar categoria!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao criar a categoria.', 'fatal');
        }
    }
}
