<?php

namespace App;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait para auditoría automática de cambios en modelos.
 * 
 * @method static void created(\Closure $callback)
 * @method static void updated(\Closure $callback)
 * @method static void deleted(\Closure $callback)
 */
trait Auditable
{
    // Columnas a excluir de la auditoría
    protected static array $auditExclude = ['id', 'created_at', 'updated_at'];

    protected static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $values = array_diff_key($model->getAttributes(), array_flip(static::$auditExclude));
            AuditLog::logChange($model, 'created', [], $values);
        });

        static::updated(function (Model $model): void {
            $oldValues = array_diff_key($model->getOriginal(), array_flip(static::$auditExclude));
            $newValues = array_diff_key($model->getAttributes(), array_flip(static::$auditExclude));
            
            // Solo auditar cambios reales
            $changes = array_diff_assoc($newValues, $oldValues);
            
            if (!empty($changes)) {
                $filteredOld = [];
                foreach ($changes as $key => $value) {
                    $filteredOld[$key] = $oldValues[$key] ?? null;
                }
                
                AuditLog::logChange($model, 'updated', $filteredOld, $changes);
            }
        });

        static::deleted(function (Model $model): void {
            $values = array_diff_key($model->getAttributes(), array_flip(static::$auditExclude));
            AuditLog::logChange($model, 'deleted', $values, []);
        });
    }
}
