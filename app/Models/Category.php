<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id'
    ];

//    public function parent(): BelongsTo
//    {
//        return $this->belongsTo(Category::class, )->withDefault();
//    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class,'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


}