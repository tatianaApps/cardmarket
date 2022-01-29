<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'simbol',
        'edition_date',
        'cards',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function cards(){
        return $this->belongsToMany(Card::class);
    }
}
