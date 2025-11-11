<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Seller\Categories;

use App\Http\Controllers\Controller;
use App\Models\Seller\Category;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyCategoryController extends Controller
{
    use SetLogTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Category $category): RedirectResponse
    {
        try {
            $category->delete();

            return redirect()->back()->with('success', 'Categoria excluída com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'error', message: 'Erro ao excluir categoria!', data: $request->all(), type: 'error', error: $th);

            return redirect()->back()->withErrors('Oops! Houve um erro ao excluir a categoria.');
        }
    }
}
