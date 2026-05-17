<?php

/**
 * @descripcion  Modelo Eloquent para la entidad SamIdentity (gama_sam_identities).
 *              Cache mínimo de identidad SAM. Implementa Authenticatable para Sanctum.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del modelo
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\Auth\SamRole;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class SamIdentity extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasApiTokens, HasFactory, SoftDeletes;

    protected $table = 'gama_sam_identities';

    protected $fillable = [
        'external_id',
        'email',
        'full_name',
        'role',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => SamRole::class,
            'last_login_at' => 'datetime',
        ];
    }

    public function findForSanctum(string $identifier): ?self
    {
        return static::where('email', $identifier)
            ->orWhere('external_id', $identifier)
            ->first();
    }
}
