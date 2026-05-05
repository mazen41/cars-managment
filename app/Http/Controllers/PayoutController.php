<?php

namespace App\Http\Controllers;

use App\Traits\HandlesExports;
use Illuminate\Http\Request;
use App\Models\Payout;
use App\Models\User;
use App\Models\Shop;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayoutExport;

class PayoutController extends Controller
{
    use HandlesExports;
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:seller_payment_history'])->only('payment_histories');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     $payments = Payment::where('seller_id', Auth::user()->seller->id)->paginate(9);
    //     return view('seller.payment_history', compact('payments'));
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function payment_histories(Request $request)
    {
        $txn_search = $request->txn_search;
        $paginate = $request->paginate ?? 15;
        $seller_id = $request->seller_id;

        $date = $request->date;
        $payment_method = $request->payment_method;

        $payments = Payout::orderBy('created_at', 'desc');

        if ($request->txn_search != null) {
            $payments->where('txn_code', $txn_search);
        }
        if ($request->seller_id != null) {
            $payments->where('seller_id', $seller_id);
        }
        if($request->date){
            $payments->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
            ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        }
        if($request->payement_method){
            $payments->where('payment_method', $payment_method);
        }
        $payments = $payments->paginate($paginate);
        return view('backend.sellers.payment_histories.index', compact('payments','txn_search','seller_id', 'date','paginate','payment_method'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find(decrypt($id));
        $payments = Payout::where('seller_id', $user->id)->orderBy('created_at', 'desc')->get();
        if ($payments->count() > 0) {
            return view('backend.sellers.payment', compact('payments', 'user'));
        }
        flash(translate('No payment history available for this seller'))->warning();
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function paymentBulkExport(Request $request)
    {
          return $this->handleBulkExport(
            $request,
            PayoutExport::class,
            'payouts_export'
        );
    }
}
