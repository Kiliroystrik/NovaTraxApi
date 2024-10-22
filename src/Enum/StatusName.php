<?php

namespace App\Enum;

final class StatusName
{
    // Statuts pour ClientOrder
    public const CONFIRMED = 'Confirmed';
    public const CANCELLED = 'Cancelled';
    public const COMPLETED = 'Completed';

    // Statuts pour Delivery
    public const SCHEDULED = 'Scheduled';
    public const IN_TRANSIT = 'In Transit';
    public const DELIVERED = 'Delivered';
    public const FAILED = 'Failed';

    // Statuts pour Tour
    public const PLANNED = 'Planned';
    public const IN_PROGRESS = 'In Progress';
    public const COMPLETED_TOUR = 'Completed';
    public const CANCELLED_TOUR = 'Cancelled';

    // Statuts pour ClientOrder et Delivery
    public const PENDING = 'Pending';
}
