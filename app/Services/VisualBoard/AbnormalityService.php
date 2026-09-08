<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;

class AbnormalityService
{
    protected AbnormalityRepositoryInterface $repository;

    public function __construct(AbnormalityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new abnormality.
     */
    public function createAbnormality(array $data): Abnormality
    {
        $data['status'] = 'open';
        $data['progress_percentage'] = 0;

        return $this->repository->create($data);
    }

    /**
     * Update progress of an abnormality.
     */
    public function updateProgress(string $id, int $percentage, ?string $actual = null, ?string $picId = null): Abnormality
    {
        $data = [
            'progress_percentage' => $percentage,
        ];

        if ($actual !== null) {
            $data['countermeasure_actual'] = $actual;
        }

        if ($picId !== null) {
            $data['pic_id'] = $picId;
        } elseif (auth()->check()) {
            // Auto set PIC if it's currently authenticated user and we don't have explicit picId
            // Only do this if the abnormality doesn't have a PIC yet (handled later or we just overwrite)
            $abnormality = $this->repository->findById($id);
            if (! $abnormality->pic_id) {
                $data['pic_id'] = auth()->id();
            }
        }

        // Determine status based on percentage
        if ($percentage == 100) {
            $data['status'] = 'resolved';
        } elseif ($percentage > 0) {
            $data['status'] = 'in_progress';
        } else {
            $data['status'] = 'open';
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Delete an abnormality.
     */
    public function deleteAbnormality(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
