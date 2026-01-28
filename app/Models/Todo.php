<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Todo extends Model
{
    use AsSource, Filterable;
    // Valikuline
    protected $table ='todos'; // Kui andmebaasi nimi ei vasta standardile, siis tuleb see siia panna

    protected $fillable = [
        'name',
        'description',
        'is_done',
        'due_at'
    ];

    // Määrab kuidas andmeid PHP-s käsitleda

    protected $casts = [
        'is_done' => 'boolean',
        'due_at' => 'datetime'
    ];

    protected $allowedSorts = [
        'name',
        'is_done',
        'due_at',
        'created_at',
        'updated_at'
    ];

    protected $allowedFilters = [
        'name' => Like::class, // LIKE %....%
        'is_done' => Where::class,  // WHERE (0/1)
    ];
}
