<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'photos',
        'link',
        'request_count',
        'status',
        'requested_by',
        'category_id',
    ];


    // Relationships
    /**
     * Get the category that owns the RequestedProduct.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, RequestedProduct>
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    /**
     * Get the user that owns the RequestedProduct.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, RequestedProduct>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Scopes

    public function scopeByUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }


    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Accessors & Mutators
    public function getMainPhotoAttribute()
    {
        return explode(',', $this->photos)[0] ?? null;
    }
    public function getPhotosArrayAttribute()
    {
        return explode(',', $this->photos);
    }
}
