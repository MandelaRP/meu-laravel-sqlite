<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Address;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddressStore extends Controller
{
    public function __construct(
        private readonly string $zip_code,
        private readonly string $address,
        private readonly string $number,
        private readonly string $city,
        private readonly string $state,
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(): void
    {
        try {
            $user = Auth::user();

            $user->addresses()->create([
                'zip_code' => $this->zip_code,
                'address'  => $this->address,
                'number'   => $this->number,
                'city'     => $this->city,
                'state'    => $this->state,
            ]);
        } catch (\Exception $e) {
            Log::channel('address')->error('Erro ao criar endereço: ' . $e->getMessage());
        }
    }
}
