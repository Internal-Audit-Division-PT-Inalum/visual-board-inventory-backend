<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\Workstation;
use App\Repositories\Contracts\WorkstationRepositoryInterface;

class WorkstationService
{
    public function __construct(
        private WorkstationRepositoryInterface $repository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createWorkstation(array $data): Workstation
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateWorkstation(string $id, array $data): ?Workstation
    {
        $this->repository->update($id, $data);

        return $this->repository->findById($id);
    }

    public function deleteWorkstation(string $id): void
    {
        $this->repository->delete($id);
    }

    public function getKioskWorkstationData(string $id): ?Workstation
    {
        return $this->repository->findWithItems($id);
    }

    public function getKioskWorkstationByEmployee(string $employeeId): ?Workstation
    {
        return $this->repository->findByEmployeeWithItems($employeeId);
    }
}
