<?php

/**
 * @descripcion  Modelo Eloquent para la entidad SamEmployee (tabla empleados en BD SAM).
 *              Establece la conexión de solo lectura a la base de datos externa de SAM.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-24 - Creación inicial del modelo de solo lectura SamEmployee
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SamEmployee extends Model
{
    /**
     * La conexión asignada al modelo.
     *
     * @var string
     */
    protected $connection = 'sam';

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'empleados';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_empleado';

    /**
     * Indica si las marcas de tiempo (timestamps) están activas.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que no se pueden asignar en masa (guarded).
     * Mantenemos vacío para solo lectura.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Scope para realizar búsquedas por nombre, apellidos o usuario.
     *
     * @param  Builder  $query
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nombre', 'LIKE', "%{$term}%")
                ->orWhere('apellidoPa', 'LIKE', "%{$term}%")
                ->orWhere('apellidoMa', 'LIKE', "%{$term}%")
                ->orWhere('usuario', 'LIKE', "%{$term}%");
        });
    }
}
