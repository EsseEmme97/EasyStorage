<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        return view('suppliers', [
            'suppliers' => Supplier::latest('created_at')->get(),
            'returnToTransactions' => $request->query('from') === 'transactions',
            'transactionInput' => $request->only([
                'description',
                'type',
                'status',
                'supplier_id',
            ]),
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $supplier = Supplier::create($validated);

        if ($request->boolean('return_to_transactions')) {
            $transactionInput = $request->only([
                'description',
                'type',
                'status',
                'supplier_id',
            ]);

            // Auto-select the newly created supplier when returning to transactions.
            $transactionInput['supplier_id'] = $supplier->id;

            return redirect()
                ->route('transactions')
                ->withInput($transactionInput)
                ->with('success', 'Fornitore creato con successo.');
        }

        return redirect()
            ->route('suppliers')
            ->with('success', 'Fornitore creato con successo.');
    }

    public function showDetails(Supplier $supplier)
    {
        $supplier->loadCount('transactions');

        return view('supplier-details', [
            'supplier' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        Supplier::destroy($supplier->getKey());

        return redirect()
            ->route('suppliers')
            ->with('success', 'Fornitore eliminato con successo.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Fornitore aggiornato con successo.');
    }
}
