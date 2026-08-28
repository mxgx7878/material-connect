<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $q = Inquiry::query()->latest();

        if ($request->filled('type'))   $q->where('type', $request->get('type'));
        if ($request->filled('status')) $q->where('status', $request->get('status'));
        if ($s = trim((string) $request->get('search', ''))) {
            $q->where(fn ($w) => $w->where('name','like',"%{$s}%")
                                   ->orWhere('company','like',"%{$s}%")
                                   ->orWhere('email','like',"%{$s}%"));
        }

        return response()->json($q->paginate((int) $request->get('per_page', 20)));
    }

    public function setStatus(Request $request, Inquiry $inquiry)
    {
        $request->validate(['status' => ['required','in:new,read,actioned,archived']]);
        $inquiry->update(['status' => $request->get('status')]);

        return response()->json(['success' => true, 'data' => $inquiry]);
    }
}