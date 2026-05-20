<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionDetailRequest;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\TransactionDetailsService;
use Illuminate\Http\Request;

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
            'availableProducts' => TransactionDetailsService::getAvailableProducts($transaction->type === 'in'),
        ]);
    }

    public function storeDetails(StoreTransactionDetailRequest $request, Transaction $transaction)
    {
        $transaction->details()->create($request->validatedPayload());

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione aggiunto con successo.');
    }
}
