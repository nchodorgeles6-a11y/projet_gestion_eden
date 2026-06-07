<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'id',
        'user_id',
        'date_debut',
        'nombre_jours',
        'date_fin',
        'notes',
        'statut',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'date_debut'    => 'date',
            'date_fin'      => 'date',
            'nombre_jours'  => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suivies()
    {
        return $this->hasMany(Suivie::class);
    }

    public function getIsActiveAttribute(): bool
    {
        $today = now()->toDateString();
        return $this->statut === 'active'
            && $this->date_debut->toDateString() <= $today
            && $this->date_fin->toDateString() >= $today;
    }
}
