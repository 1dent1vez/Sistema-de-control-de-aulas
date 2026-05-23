<?php

/**
 * @descripcion  Servicio de edificios con lógica transaccional para creación de niveles.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del servicio
 */

declare(strict_types=1);

namespace App\Services\Buildings;

use App\Models\Building;
use App\Models\Institution;
use App\Models\Level;
use App\Repositories\Contracts\BuildingRepositoryInterface;
use App\Repositories\Contracts\LevelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GamaBuildingService
{
    public function __construct(
        private readonly BuildingRepositoryInterface $buildingRepository,
        private readonly LevelRepositoryInterface $levelRepository,
    ) {}

    /**
     * Obtiene todos los edificios activos.
     *
     * @return Collection<int, Building>
     */
    public function getAll(): Collection
    {
        $activeInstitution = Institution::where('is_active', true)->first();

        return $this->buildingRepository->all($activeInstitution?->id);
    }

    /**
     * Busca un edificio por su ID.
     */
    public function getById(int $id): ?Building
    {
        return $this->buildingRepository->findById($id);
    }

    /**
     * Crea un edificio y sus niveles asociados en una transacción.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Building
    {
        $institution = Institution::find($data['institution_id']);
        if (! $institution || ! $institution->is_active) {
            throw new \RuntimeException('La institución seleccionada no está activa o no existe.');
        }

        return DB::transaction(function () use ($data): Building {
            $building = $this->buildingRepository->create($data);

            $levels = [];
            for ($i = 0; $i < $data['level_count']; $i++) {
                $levels[] = [
                    'building_id' => $building->id,
                    'name' => $i === 0 ? 'PB' : "P{$i}",
                    'display_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $this->levelRepository->insertMultiple($levels);

            return $building->fresh()->load('levels');
        });
    }

    /**
     * Actualiza un edificio existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Building
    {
        $building = $this->buildingRepository->findById($id);

        if (! $building) {
            return null;
        }

        return $this->buildingRepository->update($building, $data);
    }

    /**
     * Elimina (soft delete) un edificio.
     */
    public function delete(int $id): bool
    {
        $building = $this->buildingRepository->findById($id);

        if (! $building) {
            return false;
        }

        return $this->buildingRepository->delete($building);
    }

    /**
     * Obtiene los niveles de un edificio.
     *
     * @return Collection<int, Level>
     */
    public function getLevels(int $buildingId): Collection
    {
        return $this->levelRepository->findByBuildingId($buildingId);
    }
}
