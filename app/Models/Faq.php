<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App;
use Predis\Command\Redis\TYPE;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'question',
        'answer',
        'is_published',
        'sort_order',
        'view_count'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer'
    ];

    protected $with = ['translations'];


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($faq) {
            if (empty($faq->slug)) {
                // Generate a temporary slug, will be updated when translations are added
                $faq->slug = 'faq-' . time() . '-' . rand(1000, 9999);
            }
        });
    }

    /**
     * Get the translations for the FAQ.
     */
    public function translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }

    /**
     * Get translation for a specific field and language.
     */
    public function getTranslation($field = '', $locale = false)
    {
        $locale = $locale === false ? App::getLocale() : $locale;
        $translation = $this->translations->where('locale', $locale)->first();
        return $translation !== null ? $translation->$field : null;
    }

    /**
     * Get the translated question attribute.
     */
    public function getTranslatedQuestionAttribute()
    {
        return $this->getTranslation('question');
    }

    /**
     * Get the translated answer attribute.
     */
    public function getTranslatedAnswerAttribute()
    {
        return $this->getTranslation('answer');
    }
    /**
     * Scope a query to filter FAQs by type
     */
    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include published FAQs.
     */
    public function scopePublished(Builder $query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to order FAQs by sort order.
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Scope a query to order FAQs by popularity (view count).
     */
    public function scopePopular(Builder $query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope a query to search FAQs by question and answer content.
     */
    public function scopeSearch(Builder $query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->whereHas('translations', function ($q) use ($searchTerm) {
            $q->whereRaw('MATCH(question, answer) AGAINST(? IN BOOLEAN MODE)', [$searchTerm]);
        });
    }

    /**
     * Increment the view count for this FAQ.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Generate a unique slug for the FAQ.
     */
    protected static function generateUniqueSlug($title)
    {
        if (empty($title)) {
            return 'faq-' . time() . '-' . rand(1000, 9999);
        }

        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update the slug based on the question translation.
     */
    public function updateSlugFromQuestion($locale = 'en')
    {
        $question = $this->getTranslation('question', $locale);
        if (!empty($question)) {
            $newSlug = static::generateUniqueSlug($question);
            if ($newSlug !== $this->slug) {
                $this->update(['slug' => $newSlug]);
            }
        }
    }

}
