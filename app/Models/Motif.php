<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Motif extends Model
{
        protected $fillable = [
        'id',
        'nom',
        'type',
    ];

    public function suivies()
    {
        return $this->hasMany(Suivie::class);
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
