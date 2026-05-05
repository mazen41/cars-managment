<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CarPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cars.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the car.
     */
    public function view(User $user, Car $car): bool
    {
        // Users can view published cars or their own cars
        return $car->status === 'published' || $car->user_id === $user->id;
    }

    /**
     * Determine whether the user can create cars.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('seller') || $user->hasRole('user');
    }

    /**
     * Determine whether the user can update the car.
     */
    public function update(User $user, Car $car): bool
    {
        // Users can update their own cars or admins can update any car
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the car.
     */
    public function delete(User $user, Car $car): bool
    {
        // Users can delete their own cars or admins can delete any car
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the car.
     */
    public function restore(User $user, Car $car): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the car.
     */
    public function forceDelete(User $user, Car $car): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can publish the car.
     */
    public function publish(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can manage car features.
     */
    public function manageFeatures(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can manage car photos.
     */
    public function managePhotos(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view car analytics.
     */
    public function viewAnalytics(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can approve/reject cars.
     */
    public function moderate(User $user, Car $car): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can change car status.
     */
    public function changeStatus(User $user, Car $car): bool
    {
        // Owners can change between draft/published, admins can change any status
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($car->user_id === $user->id) {
            return in_array($car->status, ['draft', 'published']);
        }

        return false;
    }

    /**
     * Determine whether the user can view drafts.
     */
    public function viewDrafts(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can view all cars (admin panel).
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can bulk operations on cars.
     */
    public function bulkOperations(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can export cars data.
     */
    public function export(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can import cars data.
     */
    public function import(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view car reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can manage car custom fields.
     */
    public function manageCustomFields(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can feature/unfeature cars.
     */
    public function feature(User $user, Car $car): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can manage car pricing.
     */
    public function managePricing(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view car history.
     */
    public function viewHistory(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can clone cars.
     */
    public function clone(User $user, Car $car): bool
    {
        return $car->user_id === $user->id || $user->hasRole('admin');
    }
}
