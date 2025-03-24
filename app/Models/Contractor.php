<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (!$project->created_by && auth()->check()) {
                $project->created_by = auth()->id();
            }
        });

        static::updating(function ($project) {
            if (auth()->check()) {
                $project->updated_by = auth()->id();
            }
        });
    }
}
