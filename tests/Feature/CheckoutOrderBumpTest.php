<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Models\Checkout;
use App\Models\CheckoutOrderBump;
use App\Models\Seller\Product;
use App\Models\User;
use App\Services\CheckoutOrderBumpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutOrderBumpTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product1;

    private Product $product2;

    private Product $product3;

    private Checkout $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->product1 = Product::factory()->create(['user_id' => $this->user->id]);
        $this->product2 = Product::factory()->create(['user_id' => $this->user->id]);
        $this->product3 = Product::factory()->create(['user_id' => $this->user->id]);

        $this->checkout = Checkout::factory()->create([
            'product_id' => $this->product1->id,
        ]);
    }

    public function test_can_create_order_bumps_for_checkout(): void
    {
        $service = new CheckoutOrderBumpService();

        $orderBumpIds = [$this->product2->id, $this->product3->id];

        $service->createOrderBumps($this->checkout, $orderBumpIds, $this->user->id);

        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'is_active'   => true,
            'sort_order'  => 0,
        ]);

        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product3->id,
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        $this->assertEquals(2, $this->checkout->activeOrderBumps()->count());
    }

    public function test_can_update_order_bumps_for_checkout(): void
    {
        // Criar order bumps iniciais
        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 0,
            'is_active'   => true,
        ]);

        $service = new CheckoutOrderBumpService();

        // Atualizar com novos produtos
        $newOrderBumpIds = [$this->product3->id];

        $service->updateOrderBumps($this->checkout, $newOrderBumpIds, $this->user->id);

        // Verificar que o antigo foi desativado
        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'is_active'   => false,
        ]);

        // Verificar que o novo foi criado
        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product3->id,
            'is_active'   => true,
            'sort_order'  => 0,
        ]);

        $this->assertEquals(1, $this->checkout->activeOrderBumps()->count());
    }

    public function test_cannot_create_order_bumps_with_unauthorized_products(): void
    {
        $otherUser    = User::factory()->create();
        $otherProduct = Product::factory()->create(['user_id' => $otherUser->id]);

        $service = new CheckoutOrderBumpService();

        $this->expectException(\InvalidArgumentException::class);

        $service->createOrderBumps($this->checkout, [$otherProduct->id], $this->user->id);
    }

    public function test_can_remove_order_bump(): void
    {
        // Criar order bump
        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 0,
            'is_active'   => true,
        ]);

        $service = new CheckoutOrderBumpService();

        $service->removeOrderBump($this->checkout, $this->product2->id);

        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'is_active'   => false,
        ]);

        $this->assertEquals(0, $this->checkout->activeOrderBumps()->count());
    }

    public function test_can_reorder_order_bumps(): void
    {
        // Criar order bumps
        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 0,
            'is_active'   => true,
        ]);

        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product3->id,
            'sort_order'  => 1,
            'is_active'   => true,
        ]);

        $service = new CheckoutOrderBumpService();

        // Reordenar (inverter a ordem)
        $newOrder = [$this->product3->id, $this->product2->id];

        $service->reorderOrderBumps($this->checkout, $newOrder);

        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product3->id,
            'sort_order'  => 0,
        ]);

        $this->assertDatabaseHas('checkout_order_bumps', [
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 1,
        ]);
    }

    public function test_checkout_has_active_order_bumps_scope(): void
    {
        // Checkout sem order bumps
        $checkoutWithoutBumps = Checkout::factory()->create([
            'product_id' => $this->product1->id,
        ]);

        // Checkout com order bumps
        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 0,
            'is_active'   => true,
        ]);

        $checkoutsWithBumps    = Checkout::withActiveOrderBumps()->get();
        $checkoutsWithoutBumps = Checkout::withoutOrderBumps()->get();

        $this->assertTrue($checkoutsWithBumps->contains($this->checkout));
        $this->assertTrue($checkoutsWithoutBumps->contains($checkoutWithoutBumps));
    }

    public function test_checkout_model_has_helper_methods(): void
    {
        CheckoutOrderBump::create([
            'checkout_id' => $this->checkout->id,
            'product_id'  => $this->product2->id,
            'sort_order'  => 0,
            'is_active'   => true,
        ]);

        $this->assertTrue($this->checkout->hasActiveOrderBumps());
        $this->assertEquals(1, $this->checkout->active_order_bumps_count);
    }
}
