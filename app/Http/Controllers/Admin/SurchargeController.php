<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surcharge;
use App\Models\TestingFee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurchargeController extends Controller
{
    // ==================== SURCHARGES ====================

    public function indexSurcharges(Request $request)
    {
        $query = Surcharge::orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('billing_code', 'like', '%' . $request->search . '%')
                  ->orWhere('applies_to', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function showSurcharge($id)
    {
        $surcharge = Surcharge::findOrFail($id);
        return response()->json(['success' => true, 'data' => $surcharge]);
    }

    public function storeSurcharge(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'conditions'    => 'nullable|string',
            'worked_example'=> 'nullable|string',
            'billing_code'  => 'nullable|string|max:50',
            'amount'        => 'required|numeric|min:0',
            'amount_type'   => ['required', Rule::in(['fixed', 'percentage'])],
            'applies_to'    => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $surcharge = Surcharge::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Surcharge created successfully.',
            'data'    => $surcharge,
        ], 201);
    }

    public function updateSurcharge(Request $request, $id)
    {
        $surcharge = Surcharge::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'description'   => 'nullable|string',
            'conditions'    => 'nullable|string',
            'worked_example'=> 'nullable|string',
            'billing_code'  => 'nullable|string|max:50',
            'amount'        => 'sometimes|required|numeric|min:0',
            'amount_type'   => ['sometimes', 'required', Rule::in(['fixed', 'percentage'])],
            'applies_to'    => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $surcharge->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Surcharge updated successfully.',
            'data'    => $surcharge,
        ]);
    }

    public function toggleSurcharge($id)
    {
        $surcharge = Surcharge::findOrFail($id);
        $surcharge->update(['is_active' => !$surcharge->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Surcharge status updated.',
            'data'    => $surcharge,
        ]);
    }

    public function destroySurcharge($id)
    {
        $surcharge = Surcharge::findOrFail($id);
        $surcharge->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surcharge deleted successfully.',
        ]);
    }

    // ==================== TESTING FEES ====================

    public function indexTestingFees(Request $request)
    {
        $query = TestingFee::orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('billing_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function showTestingFee($id)
    {
        $fee = TestingFee::findOrFail($id);
        return response()->json(['success' => true, 'data' => $fee]);
    }

    public function storeTestingFee(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'conditions'    => 'nullable|string',
            'worked_example'=> 'nullable|string',
            'billing_code'  => 'nullable|string|max:50',
            'amount'        => 'required|numeric|min:0',
            'amount_type'   => ['required', Rule::in(['fixed', 'percentage'])],
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $fee = TestingFee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testing fee created successfully.',
            'data'    => $fee,
        ], 201);
    }

    public function updateTestingFee(Request $request, $id)
    {
        $fee = TestingFee::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'description'   => 'nullable|string',
            'conditions'    => 'nullable|string',
            'worked_example'=> 'nullable|string',
            'billing_code'  => 'nullable|string|max:50',
            'amount'        => 'sometimes|required|numeric|min:0',
            'amount_type'   => ['sometimes', 'required', Rule::in(['fixed', 'percentage'])],
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $fee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testing fee updated successfully.',
            'data'    => $fee,
        ]);
    }

    public function toggleTestingFee($id)
    {
        $fee = TestingFee::findOrFail($id);
        $fee->update(['is_active' => !$fee->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Testing fee status updated.',
            'data'    => $fee,
        ]);
    }

    public function destroyTestingFee($id)
    {
        $fee = TestingFee::findOrFail($id);
        $fee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testing fee deleted successfully.',
        ]);
    }
}