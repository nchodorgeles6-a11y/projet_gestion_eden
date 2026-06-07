<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeSuivie extends Model
{
       protected $fillable = [
        'nom',
    ];

    public function suivies()
    {
        return $this->hasMany(Suivie::class);
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
