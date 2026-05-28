<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LmsTestSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/lms_tests.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found at: $jsonPath");
            return;
        }

        $tests = json_decode(File::get($jsonPath), true);

        // Truncate existing records to prevent duplicates on rerun
        DB::table('lms_tests')->truncate();

        $this->command->info("Starting seeding of " . count($tests) . " tests...");

        foreach ($tests as $index => $item) {
            $filePath = $item['filePath'] ?? null;
            $remoteUrl = $filePath ? "https://lms-vn.cmsedu.net" . $filePath : null;

            $testType = $item['test_type'] ?? 'IG.BH';

            $this->command->info("({$index}/" . count($tests) . ") Seeding: " . ($item['evalNm'] ?? 'Unnamed'));

            DB::table('lms_tests')->insert([
                'test_type' => $testType,
                'test_cd' => $item['testGradeCd'] ?? null,
                'level_cd' => $item['testGradeCdNm'] ?? null,
                'test_seq' => $item['testSeq'] ?? null,
                'name' => $item['evalNm'] ?? 'Unnamed Test',
                'pdf_url' => $remoteUrl,
                'local_pdf_path' => $remoteUrl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Seeding lms_tests completed successfully.");
    }
}
