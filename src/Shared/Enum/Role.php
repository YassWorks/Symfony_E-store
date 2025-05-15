<?php

namespace App\Shared\Enum;

enum Role: string
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_BUYER = 'ROLE_BUYER';
    case ROLE_SELLER = 'ROLE_SELLER';
}