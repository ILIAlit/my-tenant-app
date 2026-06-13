<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Debt = 'debt';
    case Review = 'review';
    case Paid = 'paid';
}
