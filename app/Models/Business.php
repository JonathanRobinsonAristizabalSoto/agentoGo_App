<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Auditable;

class Business extends Model
{
    use HasFactory, Auditable;
    // Atributos que pueden ser asignados en masa
    protected $fillable = [
        'name',
        'slug',
        'industry_id',
        'logo',
        'primary_color',
        'secondary_color',
        'timezone',
        'status',
    ];

    // Relación: un negocio puede tener muchos usuarios
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
