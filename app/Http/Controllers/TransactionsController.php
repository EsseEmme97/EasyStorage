<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionsController extends Controller
{
    public function index()
    {
        return view('transactions', [
            'suppliers' => Supplier::all(),
            'transactions' => Transaction::with('supplier')->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:in,out'],
            'status' => ['required', 'in:draft,confirmed,canceled'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);

        Transaction::create($validated);

        return redirect()
            ->route('transactions')
            ->with('success', 'Transazione creata con successo.');
    }

    public function showDetails(Transaction $transaction)
    {
        $transaction->load(['supplier', 'details']);

        return view('transactions-details', [
            'transaction' => $transaction,
        ]);
    }

    public function storeDetails(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:sizes,kg,mt,pcs'],
            'quantity' => ['required', 'array:value,xxs,xs,s,m,l,xl'],
            'quantity.value' => [
                Rule::requiredIf(fn () => $request->input('unit') !== 'sizes'),
                'nullable',
                'numeric',
                'min:0.01',
            ],
            'quantity.xxs' => ['nullable', 'integer', 'min:0'],
            'quantity.xs' => ['nullable', 'integer', 'min:0'],
            'quantity.s' => ['nullable', 'integer', 'min:0'],
            'quantity.m' => ['nullable', 'integer', 'min:0'],
            'quantity.l' => ['nullable', 'integer', 'min:0'],
            'quantity.xl' => ['nullable', 'integer', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('unit') !== 'sizes') {
                return;
            }

            $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];
            $hasAtLeastOneSize = collect($sizeKeys)->contains(function ($size) use ($request) {
                return (int) data_get($request->input('quantity', []), $size, 0) > 0;
            });

            if (! $hasAtLeastOneSize) {
                $validator->errors()->add('quantity_sizes', 'Inserisci almeno una quantita maggiore di zero per le taglie.');
            }
        });

        $validated = $validator->validate();

        if ($validated['unit'] === 'sizes') {
            $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

            $validated['quantity'] = collect($sizeKeys)
                ->mapWithKeys(function ($size) use ($validated) {
                    return [$size => (int) data_get($validated, "quantity.$size", 0)];
                })
                ->all();
        } else {
            $validated['quantity'] = [
                'value' => (float) data_get($validated, 'quantity.value'),
            ];
        }

        $transaction->details()->create($validated);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione aggiunto con successo.');
    }
}
