<?php

namespace App\Http\Controllers;

use App\Models\RequestedProduct;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestedProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_requested_products')->only(['index', 'show']);
        $this->middleware('permission:create_requested_products')->only(['create', 'store']);
        $this->middleware('permission:edit_requested_products')->only(['edit', 'update']);
        $this->middleware('permission:delete_requested_products')->only(['destroy']);
    }
    /**
     * Display a listing of the requested products.
     */
    public function index(Request $request): View
    {

        $query = RequestedProduct::with(['category', 'user']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        $requestedProducts = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('parent_id', 0)->get();
        $users = User::where('user_type', 'customer')->get();

        return view('backend.requested_products.index', compact('requestedProducts', 'categories', 'users'));
    }

    /**
     * Show the form for creating a new requested product.
     */
    public function create(): View
    {
        $categories = Category::where('parent_id', 0)->get();
        $users = User::where('user_type', 'customer')->get();

        return view('backend.requested_products.create', compact('categories', 'users'));
    }

    /**
     * Store a newly created requested product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try{

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photos' => 'nullable|max:10',
            'link' => 'nullable|url',
            'status' => 'required|in:pending,approved,rejected,published',
            'requested_by' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        DB::beginTransaction();

        RequestedProduct::create([
            'name' => $request->name,
            'description' => $request->description,
            'photos' => $request->photos ?? [],
            'link' => $request->link,
            'request_count' => 1,
            'status' => $request->status,
            'requested_by' => $request->requested_by,
            'category_id' => $request->category_id,
        ]);
        DB::commit();

        flash(translate('Requested product has been created successfully'))->success();
        return redirect()->route('requested-products.index');

        } catch (\Exception $e) {
            DB::rollBack();
            flash($e->getMessage())->error();
            return redirect()->back();
        }

    }

    /**
     * Display the specified requested product.
     */
    public function show(RequestedProduct $requestedProduct): View
    {

        $requestedProduct->load(['category', 'user']);

        return view('backend.requested_products.show', compact('requestedProduct'));
    }

    /**
     * Show the form for editing the specified requested product.
     */
    public function edit(RequestedProduct $requestedProduct): View
    {

        $categories = Category::where('parent_id', 0)->get();
        $users = User::where('user_type', 'customer')->get();

        return view('backend.requested_products.edit', compact('requestedProduct', 'categories', 'users'));
    }

    /**
     * Update the specified requested product in storage.
     */
    public function update(Request $request, RequestedProduct $requestedProduct): RedirectResponse
    {

        try{
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photos' => 'nullable|max:20',
            'link' => 'nullable|url',
            'status' => 'required|in:pending,approved,rejected,published',
            'requested_by' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        DB::beginTransaction();

        $requestedProduct->update([
            'name' => $request->name,
            'description' => $request->description,
            'photos' => $request->photos,
            'link' => $request->link,
            'status' => $request->status,
            'requested_by' => $request->requested_by,
            'category_id' => $request->category_id,
        ]);

        DB::commit();

        flash(translate('Requested product has been updated successfully'))->success();
        return redirect()->route('requested-products.index');
        } catch(\Exception $e){
            DB::rollBack();
            flash($e->getMessage())->error();
            return redirect()->back();
        }
    }

    /**
     * Remove the specified requested product from storage.
     */
    public function destroy(RequestedProduct $requestedProduct): RedirectResponse
    {
        $requestedProduct->delete();

        flash(translate('Requested product has been deleted successfully'))->success();
        return redirect()->route('requested-products.index');
    }

    /**
     * Update status of requested product
     */
    public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'id' => 'required|exists:requested_products,id',
            'status' => 'required|in:pending,approved,rejected,published'
        ]);

        $requestedProduct = RequestedProduct::findOrFail($request->id);
        $requestedProduct->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => translate('Status updated successfully')
        ]);
    }

    /**
     * Bulk delete requested products
     */
    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete_requested_products');

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:requested_products,id'
        ]);

        RequestedProduct::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => translate('Selected requested products have been deleted successfully')
        ]);
    }
}
