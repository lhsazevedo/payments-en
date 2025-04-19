<?php

declare(strict_types=1);

namespace App\Domain;

enum UserType: int
{
    case Regular = 0;
    case Shopkeeper = 1;
}
