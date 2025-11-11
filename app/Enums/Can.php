<?php

declare(strict_types = 1);

namespace App\Enums;

enum Can: string
{
    case ViewUser   = 'view-user';
    case EditUser   = 'edit-user';
    case DeleteUser = 'delete-user';
    case CreateUser = 'create-user';
    case UpdateUser = 'update-user';
}
