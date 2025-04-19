<?php

declare(strict_types=1);

namespace App\Domain\Services\Notification;

use App\Domain\User;

interface NotificationServiceContract
{
    public function notify(User $user, NotificationContract $notification): void;
}
