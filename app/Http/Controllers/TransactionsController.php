<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionDetailRequest;
use App\Http\Requests\UpdateTransactionDetailRequest;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
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
        $availableProductSuggestions = TransactionDetailsService::getAvailableProductSuggestions($transaction->type === 'in');

        return view('transactions-details', [
            'transaction' => $transaction,
            'availableProducts' => collect($availableProductSuggestions)->pluck('name')->all(),
            'availableProductSuggestions' => $availableProductSuggestions,
        ]);
    }

    public function storeDetails(StoreTransactionDetailRequest $request, Transaction $transaction)
    {
        $transaction->details()->create($request->validatedPayload());

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione aggiunto con successo.');
    }

    public function updateDetails(
        UpdateTransactionDetailRequest $request,
        Transaction $transaction,
        TransactionDetail $transactionDetail
    ) {
        $this->ensureDetailBelongsToTransaction($transaction, $transactionDetail);

        $transactionDetail->update($request->validatedPayload());

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione aggiornato con successo.');
    }

    public function destroyDetails(Transaction $transaction, TransactionDetail $transactionDetail)
    {
        $this->ensureDetailBelongsToTransaction($transaction, $transactionDetail);

        TransactionDetail::destroy($transactionDetail->getKey());

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione eliminato con successo.');
    }

    private function ensureDetailBelongsToTransaction(Transaction $transaction, TransactionDetail $transactionDetail): void
    {
        abort_unless($transactionDetail->transaction_id === $transaction->getKey(), 404);
    }
}
