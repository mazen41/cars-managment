<?php

namespace App\Services;

use App\Models\Car;
use App\Models\User;
use App\Models\AuctionListingRequest;
use App\Models\AuctionAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class AuctionListingService
{
    /**
     * Submit a listing request for a car
     *
     * @param Car $car
     * @param User $seller
     * @param array $data
     * @return AuctionListingRequest
     */
    public function submitListingRequest(Car $car, User $seller, array $data): AuctionListingRequest
    {
        return DB::transaction(function () use ($car, $seller, $data) {
            $request = AuctionListingRequest::create([
                'car_id' => $car->id,
                'seller_id' => $seller->id,
                'requested_starting_price' => $data['requested_starting_price'],
                'requested_reserve_price' => $data['requested_reserve_price'] ?? null,
                'status' => 'pending',
            ]);

            // Log the submission
            AuctionAuditLog::create([
                'user_id' => $seller->id,
                'action' => 'listing_request_submitted',
                'details' => [
                    'request_id' => $request->id,
                    'car_id' => $car->id,
                    'requested_starting_price' => $request->requested_starting_price,
                    'requested_reserve_price' => $request->requested_reserve_price,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $request;
        });
    }

    /**
     * Approve a listing request
     *
     * @param AuctionListingRequest $request
     * @param User $admin
     * @return bool
     */
    public function approveRequest(AuctionListingRequest $request, User $admin): bool
    {
        return DB::transaction(function () use ($request, $admin) {
            if (!$request->isPending()) {
                return false;
            }

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Log the approval
            AuctionAuditLog::create([
                'user_id' => $admin->id,
                'action' => 'listing_request_approved',
                'details' => [
                    'request_id' => $request->id,
                    'car_id' => $request->car_id,
                    'seller_id' => $request->seller_id,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Reject a listing request
     *
     * @param AuctionListingRequest $request
     * @param User $admin
     * @param string $reason
     * @return bool
     */
    public function rejectRequest(AuctionListingRequest $request, User $admin, string $reason): bool
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            if (!$request->isPending()) {
                return false;
            }

            $request->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Log the rejection
            AuctionAuditLog::create([
                'user_id' => $admin->id,
                'action' => 'listing_request_rejected',
                'details' => [
                    'request_id' => $request->id,
                    'car_id' => $request->car_id,
                    'seller_id' => $request->seller_id,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Get all pending listing requests
     *
     * @return Collection
     */
    public function getPendingRequests(): Collection
    {
        return AuctionListingRequest::where('status', 'pending')
            ->with(['car', 'seller'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get listing requests for a specific seller
     *
     * @param User $seller
     * @return Collection
     */
    public function getSellerRequests(User $seller): Builder
    {
        return AuctionListingRequest::where('seller_id', $seller->id)
            ->with(['car'])
            ->orderBy('created_at', 'desc');
    }
}
