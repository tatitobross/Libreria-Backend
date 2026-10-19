<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer:id,name', 'seller:id,name', 'items.book:id,title']);
        if (! $request->user()->hasRole('Administrador')) $query->where('user_id', $request->user()->id);
        return response()->json(['data' => $query->latest()->paginate(20)]);
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless($request->user()->hasRole('Administrador') || $sale->user_id === $request->user()->id, 403);
        return response()->json(['data' => $sale->load(['customer', 'seller:id,name', 'items.book:id,title,isbn'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $sale = Sale::create(['customer_id' => $data['customer_id'] ?? null, 'user_id' => $request->user()->id, 'subtotal' => 0, 'discount_total' => 0, 'total' => 0]);
            $subtotal = 0;
            $discountTotal = 0;
            foreach ($data['items'] as $item) {
                $book = Book::whereKey($item['book_id'])->lockForUpdate()->first();
                if (! $book || ! $book->active) throw ValidationException::withMessages(['items' => 'Uno de los libros no está disponible.']);
                if ($book->stock < $item['quantity']) throw ValidationException::withMessages(['items' => "Existencias insuficientes para: {$book->title}."]);

                $quantity = $item['quantity'];
                $discountPercent = $quantity >= 10 ? 10 : ($quantity >= 5 ? 5 : 0);
                $lineSubtotal = (float) $book->price * $quantity;
                $lineDiscount = round($lineSubtotal * $discountPercent / 100, 2);
                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $sale->items()->create([
                    'book_id' => $book->id, 'quantity' => $quantity, 'unit_price' => $book->price,
                    'discount_percent' => $discountPercent, 'line_total' => round($lineSubtotal - $lineDiscount, 2),
                ]);
                $book->decrement('stock', $quantity);
            }

            $sale->update(['subtotal' => round($subtotal, 2), 'discount_total' => round($discountTotal, 2), 'total' => round($subtotal - $discountTotal, 2)]);
            return $sale;
        });

        $sale->load(['customer', 'seller:id,name', 'items.book:id,title,isbn']);

        return response()->json([
            'data' => $sale,
            'invoice' => $this->invoice($sale),
        ], 201);
    }

    public function destroy(Request $request, Sale $sale)
    {
        abort_unless($request->user()->hasRole('Administrador'), 403);
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) Book::whereKey($item->book_id)->lockForUpdate()->increment('stock', $item->quantity);
            $sale->delete();
        });
        return response()->json(['message' => 'Venta anulada y existencias restituidas.']);
    }

    private function invoice(Sale $sale): array
    {
        return [
            'number' => 'FAC-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT),
            'issued_at' => $sale->created_at?->toIso8601String(),
            'customer' => $sale->customer?->name ?? 'Consumidor final',
            'seller' => $sale->seller?->name ?? '—',
            'items' => $sale->items->map(fn ($item) => [
                'description' => $item->book?->title ?? 'Libro',
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_percent' => (float) $item->discount_percent,
                'total' => (float) $item->line_total,
            ])->values(),
            'subtotal' => (float) $sale->subtotal,
            'discount' => (float) $sale->discount_total,
            'total' => (float) $sale->total,
        ];
    }
}
