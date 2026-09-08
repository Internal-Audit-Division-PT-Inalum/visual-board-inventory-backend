<?php

namespace Tests;

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property User $user
 * @property Zone $zone
 * @property InspectionCriteria $criteria
 * @property MonthlySchedule $schedule
 * @property ScheduleRecord $record
 */
abstract class TestCase extends BaseTestCase
{
    //
}
