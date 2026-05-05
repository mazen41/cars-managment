<?php

namespace App\Traits;

trait Sortable
{
    public function scopeApplySort($query, $sort)
    {
        switch($sort) {
            case 'newest':
                return $query->latest();
            case 'oldest':
                return $query->oldest();
            default:
                return $query->latest();
        }
    }
}
