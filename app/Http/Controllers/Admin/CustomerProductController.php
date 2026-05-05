<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProduct;
use App\Models\Customer;
use App\Models\Category;
use App\Models\State;
use App\Models\City;
use App\Traits\HandlesExports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Exports\CustomerProductsExport;

class CustomerProductController extends Controller
{

    use HandlesExports;

    /**
     * Display a listing of customer products with filtering options
     */
    public function index(Request $request)
    {
        $query = CustomerProduct::with(['user', 'category', 'state', 'city', 'mainPhoto']);

        // Filter by moderation status
        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('user_id', $request->customer_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by state
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'updated_at', 'name', 'price', 'moderation_status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->paginate(20);

        // Get filter options for the view
        $customers = User::where('user_type', 'customer')->get();
        $categories = Category::all();
        $states = State::all();
        $cities = City::all();

        return view('backend.customer_products.index', compact(
            'products',
            'customers',
            'categories',
            'states',
            'cities'
        ));
    }

    /**
     * Display the specified customer product for detailed review
     */
    public function show($id)
    {
        $product = CustomerProduct::with([
            'user',
            'category',
            'state',
            'city',
            'mainPhoto',
            'photoUploads',
            'translations'
        ])->findOrFail($id);

        return view('backend.customer_products.show', compact('product'));
    }

    /**
     * Moderate a customer product (approve/reject)
     */
    public function moderate(Request $request, $id)
    {
        $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'rejection_reason' => 'required_if:action,reject|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $product = CustomerProduct::findOrFail($id);

            if ($request->action === 'approve') {
                $product->update([
                    'moderation_status' => 'approved',
                    'rejection_reason' => null
                ]);

                $message = 'Product approved successfully.';
                $alertType = 'success';
            } else {
                $product->update([
                    'moderation_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason
                ]);

                $message = 'Product rejected successfully.';
                $alertType = 'info';
            }

            // Log the moderation action
            Log::info('Customer product moderated', [
                'product_id' => $product->id,
                'action' => $request->action,
                'admin_id' => auth()->id(),
                'reason' => $request->rejection_reason
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'product' => $product->fresh()
                ]);
            }

            return redirect()->back()->with($alertType, $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moderating customer product: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while moderating the product.'
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while moderating the product.');
        }
    }

    /**
     * Bulk moderation functionality for multiple products
     */
    public function bulkModerate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:customer_products,id',
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'rejection_reason' => 'required_if:action,reject|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $productIds = $request->product_ids;
            $action = $request->action;

            $updateData = [
                'moderation_status' => $action === 'approve' ? 'approved' : 'rejected'
            ];

            if ($action === 'reject') {
                $updateData['rejection_reason'] = $request->rejection_reason;
            } else {
                $updateData['rejection_reason'] = null;
            }

            $affectedRows = CustomerProduct::whereIn('id', $productIds)
                ->update($updateData);

            // Log the bulk moderation action
            Log::info('Bulk customer product moderation', [
                'product_ids' => $productIds,
                'action' => $action,
                'admin_id' => auth()->id(),
                'affected_rows' => $affectedRows,
                'reason' => $request->rejection_reason
            ]);

            DB::commit();

            $message = $action === 'approve'
                ? "Successfully approved {$affectedRows} products."
                : "Successfully rejected {$affectedRows} products.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'affected_count' => $affectedRows
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk moderation: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred during bulk moderation.'
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during bulk moderation.');
        }
    }

    /**
     * Display analytics and statistics
     */
    public function analytics()
    {
        $stats = [
            'total_products' => CustomerProduct::count(),
            'pending_products' => CustomerProduct::where('moderation_status', 'pending')->count(),
            'approved_products' => CustomerProduct::where('moderation_status', 'approved')->count(),
            'rejected_products' => CustomerProduct::where('moderation_status', 'rejected')->count(),
        ];

        // Products by category
        $productsByCategory = CustomerProduct::select('category_id', DB::raw('count(*) as count'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Products by month (last 12 months)
        $productsByMonth = CustomerProduct::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top customers by product count
        $topCustomers = CustomerProduct::select('user_id', DB::raw('count(*) as product_count'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('product_count', 'desc')
            ->limit(10)
            ->get();

        return view('backend.customer_products.analytics', compact(
            'stats',
            'productsByCategory',
            'productsByMonth',
            'topCustomers'
        ));
    }

    /**
     * Display and update feature settings
     */
    public function settings()
    {
        // This would typically load from a settings model or config
        // For now, we'll use a basic implementation
        $settings = [
            'feature_enabled' => true,
            'max_images_per_product' => 5,
            'max_image_size_mb' => 2,
            'require_moderation' => true,
            'auto_approve_trusted_customers' => false,
        ];

        return view('backend.customer_products.settings', compact('settings'));
    }

    /**
     * Update feature settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'feature_enabled' => 'boolean',
            'max_images_per_product' => 'integer|min:1|max:20',
            'max_image_size_mb' => 'integer|min:1|max:10',
            'require_moderation' => 'boolean',
            'auto_approve_trusted_customers' => 'boolean',
        ]);

        //TODO: Save settings to a persistent storage (e.g., database or config file)
        // --- IGNORE ---

        Log::info('Customer product settings updated', [
            'admin_id' => auth()->id(),
            'settings' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    public function export(Request $request){
        return $this->handleBulkExport(
            $request,
            CustomerProductsExport::class,
            'customer_products_export'
        );
    }
}
