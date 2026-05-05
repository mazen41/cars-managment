<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FaqTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'faq_id',
        'locale',
        'question',
        'answer'
    ];

    /**
     * Get the FAQ that owns the translation.
     */
    public function faq()
    {
        return $this->belongsTo(Faq::class);
    }

    /**
     * Scope a query to search translations using fulltext search.
     */
    public function scopeFulltextSearch(Builder $query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->whereRaw('MATCH(question, answer) AGAINST(? IN BOOLEAN MODE)', [$searchTerm]);
    }

    /**
     * Scope a query to search translations using LIKE (fallback for non-fulltext search).
     */
    public function scopeLikeSearch(Builder $query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('question', 'LIKE', "%{$searchTerm}%")
              ->orWhere('answer', 'LIKE', "%{$searchTerm}%");
        });
    }
}
