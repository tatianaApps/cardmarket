<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'collection',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function collections(){
        return $this->belongsToMany(Collection::class);
    }
}
