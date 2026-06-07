<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suivie extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'type_suivie_id',
        'motif_id',
        'date',
        'justifiee',
        'conge_id',
        'permission_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeSuivie()
    {
        return $this->belongsTo(TypeSuivie::class);
    }

    public function motif()
    {
        return $this->belongsTo(Motif::class);
    }

    public function conge()
    {
        return $this->belongsTo(Conge::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    protected function casts(): array
    {
        return [
            'date'      => 'date',
            'justifiee' => 'boolean',
        ];
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
