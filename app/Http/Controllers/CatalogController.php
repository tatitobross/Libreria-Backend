<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogController extends Controller
{
    public function dashboard(Request $request)
    {
        $sales = Sale::query()->whereDate('created_at', today());
        if (! $request->user()->hasRole('Administrador')) {
            $sales->where('user_id', $request->user()->id);
        }

        return response()->json([
            'active_books' => Book::where('active', true)->count(),
            'low_stock' => Book::where('active', true)->whereBetween('stock', [1, 5])->count(),
            'out_of_stock' => Book::where('stock', 0)->count(),
            'customers' => Customer::count(),
            'sales_today' => (clone $sales)->count(),
            'sales_total_today' => (float) (clone $sales)->sum('total'),
        ]);
    }

    private function model(string $type): string
    {
        return match ($type) {
            'authors' => Author::class,
            'categories' => Category::class,
            'books' => Book::class,
            default => abort(404),
        };
    }

    public function index(string $type)
    {
        $model = $this->model($type);
        $query = $model::query();
        if ($type === 'books') $query->with(['author:id,name', 'category:id,name']);
        return response()->json(['data' => $query->orderByDesc('id')->paginate(20)]);
    }

    // En rutas con defaults, Laravel entrega primero los parámetros de URL
    // ({id}) y al final el valor por defecto (type).
    public function show(string $id, string $type)
    {
        $model = $this->model($type);
        $query = $model::query();
        if ($type === 'books') $query->with(['author:id,name', 'category:id,name']);
        return response()->json(['data' => $query->findOrFail($id)]);
    }

    public function store(Request $request, string $type)
    {
        $model = $this->model($type);
        $data = $this->validated($request, $type);
        $record = $model::create($data);
        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, string $id, string $type)
    {
        $model = $this->model($type);
        $record = $model::findOrFail($id);
        $record->update($this->validated($request, $type, $id));
        return response()->json(['data' => $record->fresh()]);
    }

    public function destroy(string $id, string $type)
    {
        $model = $this->model($type);
        $record = $model::findOrFail($id);
        if (($type === 'authors' || $type === 'categories') && $record->books()->exists()) {
            return response()->json(['message' => 'No se puede eliminar este registro porque está asignado a uno o más libros.'], 409);
        }
        if ($type === 'books' && $record->saleItems()->exists()) {
            return response()->json(['message' => 'No se puede eliminar un libro que aparece en ventas.'], 409);
        }
        $record->delete();
        return response()->json(['message' => 'Registro eliminado.']);
    }

    private function validated(Request $request, string $type, ?int $id = null): array
    {
        $rules = match ($type) {
            'authors' => ['name' => ['required', 'string', 'max:150'], 'biography' => ['nullable', 'string', 'max:2000']],
            'categories' => ['name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($id)], 'description' => ['nullable', 'string']],
            'books' => [
                'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($id)],
                'title' => ['required', 'string', 'max:200'],
                'author_id' => ['nullable', 'integer', 'exists:authors,id'],
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
                'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
                'stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string', 'max:2000'],
                'active' => ['sometimes', 'boolean'],
            ],
        };
        if ($id && $request->isMethod('PATCH')) {
            foreach ($rules as $field => $fieldRules) {
                if (($requiredAt = array_search('required', $fieldRules, true)) !== false) {
                    $rules[$field][$requiredAt] = 'sometimes|required';
                }
            }
        }

        return $request->validate($rules);
    }
}
