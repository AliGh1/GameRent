<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending'; // Payment has been initiated but is not yet completed
    case PAID = 'paid'; // Payment is completed successfully
    case FAILED = 'failed'; // Payment attempt failed (e.g., insufficient funds or error)
    case CANCELED = 'canceled'; // Payment was canceled by the user or system
    case REFUNDED = 'refunded'; // Payment was successfully refunded after being completed
}
