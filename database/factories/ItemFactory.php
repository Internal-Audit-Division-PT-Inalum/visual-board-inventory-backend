<?php

namespace Database\Factories;

use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'sku' => strtoupper($this->faker->unique()->bothify('ATK-???-###')),
            'type' => 'consumable',
            'unit' => $this->faker->randomElement(['pcs', 'rim', 'box', 'pack']),
            'current_stock' => $this->faker->numberBetween(10, 100),
            'minimum_stock' => 5,
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Create an asset type item.
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'asset',
            'unit' => 'pcs',
        ]);
    }

    /**
     * Create an item with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => 2,
            'minimum_stock' => 5,
        ]);
    }
}
