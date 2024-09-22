<?php

namespace App\Enums;

enum RentalStatus: string
{
    case PENDING = 'pending';   // Rental request has been made, but payment is not yet processed or approved.
    case ACTIVE = 'active';     // The rental is currently in use.
    case EXPIRED = 'expired';   // The rental period has ended.
    case RETURNED = 'returned'; // The rental has been deactivated or returned.
    case SUSPENDED = 'suspended'; // The rental is temporarily suspended due to issues.
    case CANCELED = 'canceled'; // The rental was cancelled before it was processed or completed.
}
