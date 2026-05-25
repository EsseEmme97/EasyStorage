<?php

use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('it shows only outstanding outside quantity after in out in cycle for value unit', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $initialSupplier = Supplier::create(['name' => 'Supplier A']);
    $processingSupplier = Supplier::create(['name' => 'Supplier B']);

    $initialLoad = Transaction::create([
        'description' => 'Ingresso iniziale',
        'type' => 'in',
        'status' => 'confirmed',
        'supplier_id' => $initialSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $initialLoad->id,
        'product_name' => 'Jeans Basic',
        'unit' => 'pcs',
        'quantity' => ['value' => 100],
    ]);

    $outboundForProcessing = Transaction::create([
        'description' => 'Invio lavorazione',
        'type' => 'out',
        'status' => 'confirmed',
        'supplier_id' => $processingSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $outboundForProcessing->id,
        'product_name' => 'Jeans Basic',
        'unit' => 'pcs',
        'quantity' => ['value' => 30],
    ]);

    $partialReturn = Transaction::create([
        'description' => 'Rientro parziale',
        'type' => 'in',
        'status' => 'confirmed',
        'supplier_id' => $processingSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $partialReturn->id,
        'product_name' => 'Jeans Basic',
        'unit' => 'pcs',
        'quantity' => ['value' => 20],
    ]);

    actingAs($user);
    $response = get(route('inventory.dashboard'));

    $response->assertSuccessful();

    $row = collect($response->viewData('rows'))->first();

    expect((float) data_get($row, 'in.value'))->toBe(120.0)
        ->and((float) data_get($row, 'out.value'))->toBe(30.0)
        ->and((float) data_get($row, 'in_storage.value'))->toBe(90.0)
        ->and((float) data_get($row, 'outside_storage.value'))->toBe(10.0);
});

test('it computes outstanding outside quantities per size after partial returns', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $initialSupplier = Supplier::create(['name' => 'Supplier C']);
    $processingSupplier = Supplier::create(['name' => 'Supplier D']);

    $initialLoad = Transaction::create([
        'description' => 'Ingresso taglie',
        'type' => 'in',
        'status' => 'confirmed',
        'supplier_id' => $initialSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $initialLoad->id,
        'product_name' => 'T-Shirt Fit',
        'unit' => 'sizes',
        'quantity' => ['xxs' => 0, 'xs' => 0, 's' => 10, 'm' => 10, 'l' => 0, 'xl' => 0],
    ]);

    $outboundForProcessing = Transaction::create([
        'description' => 'Uscita taglie',
        'type' => 'out',
        'status' => 'confirmed',
        'supplier_id' => $processingSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $outboundForProcessing->id,
        'product_name' => 'T-Shirt Fit',
        'unit' => 'sizes',
        'quantity' => ['xxs' => 0, 'xs' => 0, 's' => 4, 'm' => 3, 'l' => 0, 'xl' => 0],
    ]);

    $partialReturn = Transaction::create([
        'description' => 'Rientro taglie',
        'type' => 'in',
        'status' => 'confirmed',
        'supplier_id' => $processingSupplier->id,
    ]);
    TransactionDetail::create([
        'transaction_id' => $partialReturn->id,
        'product_name' => 'T-Shirt Fit',
        'unit' => 'sizes',
        'quantity' => ['xxs' => 0, 'xs' => 0, 's' => 2, 'm' => 1, 'l' => 0, 'xl' => 0],
    ]);

    actingAs($user);
    $response = get(route('inventory.dashboard'));

    $response->assertSuccessful();

    $row = collect($response->viewData('rows'))->first();

    expect((int) data_get($row, 'outside_storage.s'))->toBe(2)
        ->and((int) data_get($row, 'outside_storage.m'))->toBe(2)
        ->and((int) data_get($row, 'totals.outside_storage'))->toBe(4)
        ->and((int) data_get($row, 'totals.in_storage'))->toBe(16);
});
