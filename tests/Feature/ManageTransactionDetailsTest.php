<?php

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

test('it updates a transaction detail', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $transaction = Transaction::create([
        'description' => 'Carico primavera',
        'type' => 'in',
        'status' => 'confirmed',
    ]);
    $detail = TransactionDetail::create([
        'transaction_id' => $transaction->id,
        'product_name' => 'Kick pant',
        'unit' => 'pcs',
        'quantity' => ['value' => 10],
    ]);

    actingAs($user);

    $response = put(route('transactions.details.update', [$transaction, $detail]), [
        'product_name' => 'Kick pant premium',
        'unit' => 'pcs',
        'quantity' => ['value' => 14.5],
    ]);

    $response->assertRedirect(route('transactions.show', $transaction));

    $detail->refresh();

    expect($detail->product_name)->toBe('Kick pant premium');
    expect((float) data_get($detail->quantity, 'value'))->toBe(14.5);
});

test('it deletes a transaction detail', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $transaction = Transaction::create([
        'description' => 'Scarico magazzino',
        'type' => 'out',
        'status' => 'draft',
    ]);
    $detail = TransactionDetail::create([
        'transaction_id' => $transaction->id,
        'product_name' => 'T-Shirt',
        'unit' => 'sizes',
        'quantity' => ['xxs' => 0, 'xs' => 1, 's' => 2, 'm' => 3, 'l' => 1, 'xl' => 0],
    ]);

    actingAs($user);

    $response = delete(route('transactions.details.destroy', [$transaction, $detail]));

    $response->assertRedirect(route('transactions.show', $transaction));
    expect(TransactionDetail::query()->whereKey($detail->id)->exists())->toBeFalse();
});
