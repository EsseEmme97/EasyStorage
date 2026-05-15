<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Supplier;

class TransactionsController extends Controller
{
    public function index() {
        return view('transactions', [
            'suppliers' => Supplier::all(),
        ]);
    }

    public function create(Request $request) {
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
}
