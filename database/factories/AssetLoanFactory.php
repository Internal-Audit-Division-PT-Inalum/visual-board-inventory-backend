<?php

namespace Database\Factories;

use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetLoan>
 */
class AssetLoanFactory extends Factory
{
    protected $model = AssetLoan::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory()->asset(),
            'employee_id' => Employee::factory(),
            'quantity' => 1,
            'returned_quantity' => 0,
            'borrowed_at' => now(),
            'fully_returned_at' => null,
            'borrow_ledger_id' => InventoryLedger::factory()->state(['type' => 'borrow']),
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'borrowed_at' => now()->subDays(10),
            'returned_quantity' => 0,
            'fully_returned_at' => null,
        ]);
    }

    public function settled(): static
    {
        return $this->state(fn () => [
            'quantity' => 2,
            'returned_quantity' => 2,
            'borrowed_at' => now()->subDays(5),
            'fully_returned_at' => now()->subDays(1),
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn () => [
            'quantity' => 3,
            'returned_quantity' => 1,
            'borrowed_at' => now()->subDays(2),
            'fully_returned_at' => null,
        ]);
    }
}
