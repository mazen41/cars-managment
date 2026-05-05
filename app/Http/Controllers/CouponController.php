<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\User;

class CouponController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_all_coupons'])->only('index');
        $this->middleware(['permission:add_coupon'])->only('create', 'store');
        $this->middleware(['permission:edit_coupon'])->only('edit');
        $this->middleware(['permission:delete_coupon'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
    * @return \Illuminate\View\View
     */
    public function index()
    {
        $admins_staff = User::where('user_type', 'admin')->orWhere('user_type', 'staff')->pluck('id');
        $coupons = Coupon::whereIn('user_id', $admins_staff)->orderBy('id','desc')->get();
        return view('backend.marketing.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
    * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('backend.marketing.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CouponRequest $request)
    {
        $user_id = auth()->user()->id;
        $status = $request->type == 'welcome_base' ? 0 : 1;
        $coupon = Coupon::create($request->validated() + [
            'user_id' => $user_id,
            'status' => $status,
        ]);
        if(!$request->is_welcome_coupon && $request->user_ids){
            $coupon->users()->sync($request->user_ids);
        }
        flash(translate('Coupon has been saved successfully'))->success();
        return redirect()->route('coupon.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail(decrypt($id));
        return view('backend.marketing.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());
        if($request->welcome_coupon){
            if($request->user_ids){
                $coupon->users()->sync($request->user_ids);
            } else {
            $coupon->users()->sync([]);
            }
        }
        flash(translate('Coupon has been updated successfully'))->success();
        return redirect()->route('coupon.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   $coupon = Coupon::findOrFail($id);
        $coupon->users()->sync([]);
        $coupon->destroy($id);
        flash(translate('Coupon has been deleted successfully'))->success();
        return redirect()->route('coupon.index');
    }

    public function get_coupon_form(Request $request)
    {
        switch ($request->coupon_type) {
            case 'product_base':
                $admin_ids = User::where('user_type', 'admin')->orWhere('user_type', 'staff')->pluck('id');
                $products = filter_products(\App\Models\Product::whereIn('user_id', $admin_ids))->get();
                return view('partials.coupons.product_base_coupon', compact('products'));
            case 'cart_base':
                return view('partials.coupons.cart_base_coupon');
            case 'welcome_base':
                return view('partials.coupons.welcome_base_coupon');
            default:
                return response()->json(['error' => 'Invalid coupon type']);
        }
    }

    public function get_coupon_form_edit(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        switch ($request->coupon_type) {
            case 'product_base':
                $admin_ids = User::where('user_type', 'admin')->orWhere('user_type', 'staff')->pluck('id');
                $products = filter_products(\App\Models\Product::whereIn('user_id', $admin_ids))->get();
                return view('partials.coupons.product_base_coupon_edit', compact('products',  'coupon'));
            case 'cart_base':
                return view('partials.coupons.cart_base_coupon_edit', compact('coupon'));
            case 'welcome_base':
                return view('partials.coupons.welcome_base_coupon_edit', compact('coupon'));
            default:
                return response()->json(['error' => 'Invalid coupon type']);
        }
    }

    public function updateStatus(Request $request)
    {
        foreach (Coupon::where('type', 'welcome_base')->get() as $welcome_coupon) {
            $welcome_coupon->status = 0;
            $welcome_coupon->save();
        }

        $coupon = Coupon::findOrFail($request->id);
        $coupon->status = $request->status;
        if ($coupon->save()) {
            return 1;
        }
        return 0;
    }

}
