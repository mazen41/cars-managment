<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarCategory extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'name',
        'description',
        'status',
        'order',
        'parent_id',
        'is_default',
        'image',
    ];

    protected $casts = [
        'order' => 'integer',
        'parent_id' => 'integer',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(CarCategory::class, 'parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the cars for the category
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'category_id');
    }

    /**
     * Get the translations for the category
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CarCategoryTranslation::class, 'car_category_id');
    }

    /**
     * Get the CarBrands for the category
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(CarBrand::class, 'car_brand_car_category', 'car_category_id', 'car_brand_id');
    }

    // Scopes

    /**
     * Scope a query to only include active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include parent categories
     */
    public function scopeParents($query)
    {
        return $query->where('parent_id', 0);
    }

    /**
     * Scope a query to only include child categories
     */
    public function scopeChildren($query)
    {
        return $query->where('parent_id', '>', 0);
    }

    /**
     * Scope a query to only include default categories
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to order categories by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Accessors & Mutators

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? uploaded_asset($this->image) : null;
    }

    /**
     * Get the category name with fallback to translation
     */
    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->name : $this->name;
    }

    /**
     * Get the full category path
     */
    public function getFullPathAttribute()
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get the category level (depth)
     */
    public function getLevelAttribute()
    {
        $level = 0;
        $category = $this;

        while ($category->parent) {
            $level++;
            $category = $category->parent;
        }

        return $level;
    }

    // Helper methods

    /**
     * Get translated attribute
     */
    public function getTranslatedAttribute($attribute, $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        $translation = $this->translations()->where('lang', $locale)->first();

        return $translation ? $translation->$attribute : $this->$attribute;
    }

    /**
     * Check if category has children
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Check if category has cars
     */
    public function hasCars()
    {
        return $this->cars()->exists();
    }

    /**
     * Get cars count
     */
    public function getCarsCountAttribute()
    {
        return $this->cars()->count();
    }

    /**
     * Get children count
     */
    public function getChildrenCountAttribute()
    {
        return $this->children()->count();
    }

    /**
     * Check if category is parent
     */
    public function isParent()
    {
        return $this->parent_id == 0;
    }

    /**
     * Check if category is child
     */
    public function isChild()
    {
        return $this->parent_id > 0;
    }

    /**
     * Check if category is default
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $category = $this->parent;

        while ($category) {
            $ancestors->push($category);
            $category = $category->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendants as flat collection
     */
    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Check if category can be deleted
     */
    public function canBeDeleted()
    {
        return !$this->hasCars() && !$this->hasChildren() && !$this->isDefault();
    }
}
