<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\Workstation;
use App\Repositories\Contracts\WorkstationRepositoryInterface;

class WorkstationService
{
    public function __construct(
        private WorkstationRepositoryInterface $repository
    ) {}

    public function createWorkstation(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateWorkstation(string $id, array $data)
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
