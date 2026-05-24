<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra un cambio en el audit log.
     * 
     * @param Model $model - Modelo que fue modificado
     * @param string $action - Tipo de acción (created, updated, deleted)
     * @param array<string, mixed> $oldValues - Valores anteriores
     * @param array<string, mixed> $newValues - Valores nuevos
     * @param int|null $userId - ID del usuario (usa auth()->id() si es null)
     */
    public static function logChange(Model $model, string $action, array $oldValues = [], array $newValues = [], ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        
        if ($userId === null) {
            return;
        }

        /** @var Request|null $req */
        $req = request();
        
        self::create([
            'user_id' => $userId,
            'model_type' => $model::class,
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => $req?->ip(),
            'user_agent' => $req?->userAgent(),
        ]);
    }
}
