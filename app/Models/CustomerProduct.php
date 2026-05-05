<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App;

class CustomerProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'condition',
        'price',
        'category_id',
        'main_photo',
        'photos',
        'address',
        'state_id',
        'city_id',
        'longitude',
        'latitude',
        'moderation_status',
        'availability_status',
        'rejection_reason',
    ];

    protected $casts = [
        'photos' => 'array',
        'price' => 'decimal:2',
        'longitude' => 'decimal:8',
        'latitude' => 'decimal:8',
    ];

    protected $with = ['translations'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function mainPhoto(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'main_photo');
    }

    public function photoUploads(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class, 'customer_product_photos', 'customer_product_id', 'upload_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CustomerProductTranslation::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    public function scopeByLocation($query, $stateId, $cityId = null)
    {
        $query->where('state_id', $stateId);
        
        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        
        return $query;
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePending($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('moderation_status', 'rejected');
    }

    // Accessors/Mutators
    public function getPhotosArrayAttribute()
    {
        if (is_string($this->photos)) {
            return json_decode($this->photos, true) ?? [];
        }
        
        return $this->photos ?? [];
    }

    public function setPhotosAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['photos'] = json_encode($value);
        } else {
            $this->attributes['photos'] = $value;
        }
    }

    // Methods
    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $translation = $this->translations->where('lang', $lang)->first();
        return $translation != null ? $translation->$field : $this->$field;
    }

    public function isOwnedBy($userId)
    {
        return $this->user_id == $userId;
    }

    public function isApproved()
    {
        return $this->moderation_status === 'approved';
    }

    public function isPending()
    {
        return $this->moderation_status === 'pending';
    }

    public function isRejected()
    {
        return $this->moderation_status === 'rejected';
    }

    public function isAvailable()
    {
        return $this->availability_status === 'available';
    }

    public function approve()
    {
        $this->update(['moderation_status' => 'approved', 'rejection_reason' => null]);
    }

    public function reject($reason = null)
    {
        $this->update(['moderation_status' => 'rejected', 'rejection_reason' => $reason]);
    }

    public function resetTopending()
    {
        $this->update(['moderation_status' => 'pending', 'rejection_reason' => null]);
    }
}
