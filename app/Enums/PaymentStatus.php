<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
