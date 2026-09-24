<?php

namespace App\Filament\Resources\FiveREvaluations\Pages;

use App\Filament\Resources\FiveREvaluations\FiveREvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EditFiveREvaluation extends EditRecord
{
    protected static string $resource = FiveREvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['evaluation_file'])) {
            $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $data['evaluation_file']);
            $filePath = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . $relativePath);

            try {
                Log::info('5R Eval: evaluation_file value', ['value' => $data['evaluation_file']]);
                Log::info('5R Eval: resolved filePath', ['path' => $filePath, 'exists' => file_exists($filePath)]);

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
                                Log::info('5R Eval: found "nilai total" label', ['row' => $rowIndex, 'col' => $col, 'cellValue' => $cellValue]);
                                $foundLabel = true;
                                break;
                            }
                        }

                        if ($foundLabel) {
                            foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $valCol) {
                                $val = $worksheet->getCell($valCol . $rowIndex)->getCalculatedValue();
                                Log::info('5R Eval: checking cell for score', ['col' => $valCol, 'row' => $rowIndex, 'val' => $val, 'is_numeric' => is_numeric($val)]);
                                if (is_numeric($val) && $val > 0) {
                                    $totalScore = $val;
                                    Log::info('5R Eval: score found!', ['score' => $totalScore]);
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($totalScore !== null) {
                        $data['total_score'] = $totalScore;
                    } else {
                        Log::warning('5R Eval: score not found, keeping 0');
                    }
                } else {
                    Log::warning('5R Eval: file does not exist at path', ['path' => $filePath]);
                }
            } catch (\Exception $e) {
                Log::error('5R Eval: exception during parsing', ['error' => $e->getMessage()]);
            }
        }

        return $data;
    }
}
