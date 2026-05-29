<?php

/**
 * @descripcion  Controlador API para identidades SAM.
 *              Métodos: index, searchSamEmployees, search, assignRole, searchLocalTeachers, setPassword, destroy.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.2.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-24
 */
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Auth\SamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AssignRoleRequest;
use App\Http\Requests\Auth\ConfirmAdminPasswordRequest;
use App\Http\Resources\Auth\SamEmployeeResource;
use App\Http\Resources\Auth\SamProfileResource;
use App\Models\SamEmployee;
use App\Models\SamIdentity;
use App\Services\Auth\SamRoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SamIdentityController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SamRoleService $samRoleService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);

        return $this->success(SamProfileResource::collection($this->samRoleService->listAll()), 'Identidades recuperadas.');
    }

    public function searchSamEmployees(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $emps = SamEmployee::search($request->query('q'))->limit(20)->get();

        return $this->success(SamEmployeeResource::collection($emps), 'Empleados recuperados.');
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);
        $query = $request->query('q', '');
        if (empty($query)) {
            return $this->error('Búsqueda obligatoria.', 422);
        }

        return $this->success($this->samRoleService->searchInSam($query), 'Resultados.');
    }

    public function assignRole(string $extId, AssignRoleRequest $request): JsonResponse
    {
        $this->authorize('create', SamIdentity::class);
        $role = SamRole::from($request->input('role'));

        $samIdentity = SamIdentity::where('external_id', $extId)->first();
        if ($samIdentity && $samIdentity->role === SamRole::ADMIN && $role === SamRole::TEACHER) {
            return $this->error('No puedes degradar un administrador a docente. Usa la opción de eliminar si es necesario.', 422);
        }

        if ($role === SamRole::ADMIN) {
            $user = $request->user();
            if ($user->password === null) {
                return $this->error('Debes configurar una contraseña de administrador antes de realizar esta acción.', 422);
            }
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return $this->error('Contraseña de administrador incorrecta.', 403);
            }
        }

        return $this->success(new SamProfileResource($this->samRoleService->assignRole($extId, $role)), 'Rol asignado.', 200);
    }

    public function searchLocalTeachers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);
        $q = $request->query('q', '');
        $teachers = SamIdentity::where('role', SamRole::TEACHER)
            ->when(! empty($q), fn ($query) => $query->where(fn ($sub) => $sub->where('external_id', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('full_name', 'like', "%{$q}%")
            ))->limit(20)->get();

        return $this->success(SamProfileResource::collection($teachers), 'Docentes recuperados.');
    }

    public function setPassword(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return $this->success(null, 'Contraseña configurada.');
    }

    public function destroy(string $extId, ConfirmAdminPasswordRequest $request): JsonResponse
    {
        $this->authorize('delete', SamIdentity::class);
        $identity = SamIdentity::where('external_id', $extId)->firstOrFail();
        if ($identity->sam_id === $request->user()->sam_id) {
            return $this->error('No puedes auto-eliminarte.', 400);
        }
        $identity->forceDelete();

        return $this->success(null, 'Usuario eliminado.');
    }
}
