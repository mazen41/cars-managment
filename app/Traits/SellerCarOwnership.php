<?php

namespace App\Traits;

use App\Models\Car;
use App\Models\CarReservation;
use App\Models\CarInspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait SellerCarOwnership
{
    /**
     * Verify that the authenticated seller owns the specified car.
     *
     * @param int $carId
     * @return Car|null Returns Car model if owned, null otherwise
     */
    protected function verifyCarOwnership(int $carId): ?Car
    {
        return Car::where('user_id', Auth::id())->find($carId);
    }

    /**
     * Verify that the authenticated seller owns the car associated with the reservation.
     *
     * @param CarReservation $reservation
     * @return bool
     */
    protected function verifyReservationOwnership(CarReservation $reservation): bool
    {
        return $reservation->car && $reservation->car->user_id === Auth::id();
    }

    /**
     * Verify that the authenticated seller owns the car associated with the inspection.
     *
     * @param CarInspection $inspection
     * @return bool
     */
    protected function verifyInspectionOwnership(CarInspection $inspection): bool
    {
        return $inspection->car && $inspection->car->user_id === Auth::id();
    }
}
