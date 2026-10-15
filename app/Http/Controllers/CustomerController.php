<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index() { return response()->json(['data' => Customer::orderByDesc('id')->paginate(20)]); }
    public function show(Customer $customer) { return response()->json(['data' => $customer]); }
    public function store(Request $request) { return response()->json(['data' => Customer::create($this->validateData($request))], 201); }
    public function update(Request $request, Customer $customer) { $customer->update($this->validateData($request, $customer)); return response()->json(['data' => $customer->fresh()]); }
    public function destroy(Customer $customer) { $customer->delete(); return response()->json(['message' => 'Cliente eliminado.']); }
    private function validateData(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:250'],
        ];
        if ($customer && $request->isMethod('PATCH')) {
            foreach ($rules as $field => $fieldRules) {
                if (($requiredAt = array_search('required', $fieldRules, true)) !== false) $rules[$field][$requiredAt] = 'sometimes|required';
            }
        }
        return $request->validate($rules);
    }
}
