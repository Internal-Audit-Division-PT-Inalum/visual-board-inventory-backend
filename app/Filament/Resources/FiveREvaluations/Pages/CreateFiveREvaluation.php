<?php

namespace App\Filament\Resources\FiveREvaluations\Pages;

use App\Filament\Resources\FiveREvaluations\FiveREvaluationResource;
use Filament\Resources\Pages\CreateRecord;
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

                    // Search for "Nilai Total" in the first few columns
                    foreach ($worksheet->getRowIterator() as $row) {
                        $rowIndex = $row->getRowIndex();
                        $foundLabel = false;

                        // Check columns A to D for the label
                        foreach (['A', 'B', 'C', 'D'] as $col) {
                            $cellValue = $worksheet->getCell($col . $rowIndex)->getCalculatedValue();
                            if (str_contains(strtolower((string) $cellValue), 'nilai total')) {
                                $foundLabel = true;
                                break;
                            }
                        }

                        if ($foundLabel) {
                            // Search the rest of the row for a numeric value
                            foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $valCol) {
                                $val = $worksheet->getCell($valCol . $rowIndex)->getCalculatedValue();
                                if (is_numeric($val) && $val > 0) {
                                    $totalScore = $val;
                                    break 2; // Break both loops
                                }
                            }
                        }
                    }

                    if ($totalScore !== null) {
                        $data['total_score'] = $totalScore;
                    }
                }
            } catch (\Exception $e) {
                // Log exception if needed, fallback to 0
            }
        }

        return $data;
    }
}
