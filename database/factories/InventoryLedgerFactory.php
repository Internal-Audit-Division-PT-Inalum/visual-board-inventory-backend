<?php

namespace Database\Factories;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLedger>
 */
class InventoryLedgerFactory extends Factory
{
    protected $model = InventoryLedger::class;

    public function definition(): array
    {
        $stockBefore = $this->faker->numberBetween(10, 100);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'type' => 'out',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore - $quantity,
            'notes' => $this->faker->optional()->sentence(),
            'reference_number' => null,
        ];
    }
}
