<?php

namespace App\Filament\Resources\FiveREvaluations\Pages;

use App\Filament\Resources\FiveREvaluations\FiveREvaluationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CreateFiveREvaluation extends CreateRecord
{
    protected static string $resource = FiveREvaluationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_score'] = 0; // Default

        if (isset($data['evaluation_file'])) {
            $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $data['evaluation_file']);
            $filePath = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . $relativePath);

            try {
                if (file_exists($filePath)) {
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();

                    $totalScore = null;

                    foreach ($worksheet->getRowIterator() as $row) {
                        $rowIndex = $row->getRowIndex();
                        $foundLabel = false;

                        foreach (['A', 'B', 'C', 'D'] as $col) {
                            $cellValue = $worksheet->getCell($col . $rowIndex)->getCalculatedValue();
                            if (str_contains(strtolower((string) $cellValue), 'nilai total')) {
                                $foundLabel = true;
                                break;
                            }
                        }

                        if ($foundLabel) {
                            foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $valCol) {
                                $val = $worksheet->getCell($valCol . $rowIndex)->getCalculatedValue();
                                if (is_numeric($val) && $val > 0) {
                                    $totalScore = $val;
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($totalScore !== null) {
                        $data['total_score'] = $totalScore;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Gagal membaca nilai total evaluasi 5R dari berkas Excel: ' . $e->getMessage());
            }
        }

        return $data;
    }
}
