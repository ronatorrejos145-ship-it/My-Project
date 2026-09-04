<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetImportService
{
    public function __construct(protected AssetReceivingService $receivingService) {}

    public function importCsv(string $filePath, ?int $userId = null): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Import CSV file not found at '{$filePath}'.");
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new InvalidArgumentException("CSV file is empty or invalid format.");
        }

        $imported = [];
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) < count($header)) {
                    continue;
                }

                $data = array_combine($header, $row);

                try {
                    $asset = $this->receivingService->receiveAsset([
                        'asset_category_id' => $data['category_id'] ?? 1,
                        'asset_model_id' => $data['model_id'] ?? null,
                        'serial_number' => $data['serial_number'] ?? null,
                        'mac_address' => $data['mac_address'] ?? null,
                        'manufacturer' => $data['manufacturer'] ?? null,
                        'purchase_cost' => $data['purchase_cost'] ?? 0.00,
                        'condition' => $data['condition'] ?? 'NEW',
                        'notes' => 'Imported via CSV file.',
                    ], $userId);

                    $imported[] = $asset;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                }
            }

            if (count($errors) > 0 && count($imported) === 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return [
            'imported_count' => count($imported),
            'errors' => $errors,
        ];
    }
}
