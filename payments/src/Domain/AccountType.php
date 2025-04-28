<?php

declare(strict_types=1);

namespace App\Domain;

enum AccountType: int
{
    case Regular = 0;
    case Shopkeeper = 1;
}
