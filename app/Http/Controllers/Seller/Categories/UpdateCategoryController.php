<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Categories\UpdateCategoryRequest;
use App\Models\Seller\Category;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCategoryController extends Controller
{
    use SetLogTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $category->update($request->validated());

            return redirect()->back()->with('success', 'Categoria atualizada com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao atualizar categoria!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao atualizar a categoria.');
        }
    }
}
