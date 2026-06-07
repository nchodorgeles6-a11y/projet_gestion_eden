<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'type_contrat',
        'lieu_habitation',
        'sexe',
        'salaire_base',
        'mode_paiement',
        'prime_transport',
        'prime_logement',
        'prime_fonction',
        'prime_rendement',
        'prime_panier',
        'bonus_annuel',
        'cnps',
        'assurance_maladie',
        'avantages_nature',
        'date_embauche',
        'date_naissance',
        'date_fin_contrat',
        'password',
        'acces_systeme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
 

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'date_embauche'      => 'date',
            'date_naissance'     => 'date',
            'date_fin_contrat'   => 'date',
            'cnps'               => 'boolean',
            'assurance_maladie'  => 'boolean',
            'avantages_nature'   => 'array',
        ];
    }

     public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function suivies()
    {
        return $this->hasMany(Suivie::class);
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    // Droit légal ivoirien : 2,5 jours/mois travaillé = 30 jours/an
    const CONGE_ANNUEL = 30;

    public function soldeConges(int $annee = null): array
    {
        $annee ??= now()->year;

        $joursUtilises = $this->conges()
            ->where('statut', 'approuve')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn ($c) => $c->date_debut && $c->date_fin
                ? $c->date_debut->diffInDays($c->date_fin) + 1
                : 1
            );

        $restant = max(0, self::CONGE_ANNUEL - $joursUtilises);

        return [
            'droit'      => self::CONGE_ANNUEL,
            'utilises'   => (int) $joursUtilises,
            'restant'    => $restant,
            'annee'      => $annee,
        ];
    }

    public $incrementing = false;
    protected $keyType = 'string';
}

