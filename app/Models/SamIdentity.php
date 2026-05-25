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
 * @version      1.1.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-17 - Creación inicial del modelo
 *               2026-05-25 - Corrección: prevenir duplicación de email si external_id ya contiene arroba en getProfileFromSam().
 *               2026-05-25 - Adición de trait Notifiable y relación custom notifications.
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\Auth\SamRole;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SamIdentity extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'gama_sam_identities';

    protected $fillable = [
        'external_id',
        'role',
        'last_login_at',
        'password',
    ];

    protected $hidden = [
        'password',
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

    /**
     * Determina si la identidad tiene rol de Administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === SamRole::ADMIN;
    }

    /**
     * Obtiene en tiempo real los datos del perfil (nombre y correo) del empleado en SAM.
     * Consulta directamente el modelo SamEmployee sobre la conexión 'sam'.
     *
     * @return array{fullName: string, email: string}
     */
    public function getProfileFromSam(): array
    {
        try {
            $employee = SamEmployee::find($this->external_id);

            if ($employee !== null) {
                $fullName = trim(($employee->nombre ?? '').' '.($employee->apellidoPa ?? '').' '.($employee->apellidoMa ?? ''));

                return [
                    'fullName' => $fullName ?: 'Sin nombre',
                    'email' => $employee->correo ?? '',
                ];
            }
        } catch (\Throwable $e) {
            // Ignorar y proceder al fallback
        }

        return [
            'fullName' => $this->full_name ?? 'Usuario SAM',
            'email' => $this->email ?? (str_contains($this->external_id, '@') ? $this->external_id : $this->external_id.'@toluca.tecnm.mx'),
        ];
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(GamaNotification::class, 'notifiable')->latest();
    }
}
