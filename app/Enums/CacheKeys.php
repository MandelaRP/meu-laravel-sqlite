<?php

declare(strict_types = 1);

namespace App\Enums;

use Illuminate\Support\Facades\Auth;

enum CacheKeys: string
{
    case GROUPS  = 'groups';
    case USERS   = 'users';
    case MEMBERS = 'members';

    /**
     * Gera uma chave de cache baseada no recurso
     *
     * @param int|string|null $resourceId ID do recurso específico
     * @param string|null $suffix Sufixo adicional
     * @param bool $includeUserId Se deve incluir o ID do usuário autenticado
     */
    public function key(int | string | null $resourceId = null, ?string $suffix = null, bool $includeUserId = true): string
    {
        $key = $this->value;

        if ($includeUserId && ($userId = Auth::id())) {
            $key .= ":{$userId}";
        }

        if ($resourceId !== null && $resourceId !== '' && $resourceId !== '0' && $resourceId !== 0) {
            $key .= ":{$resourceId}";
        }

        if ($suffix !== null && $suffix !== '' && $suffix !== '0') {
            $key .= ":{$suffix}";
        }

        return $key;
    }
}
