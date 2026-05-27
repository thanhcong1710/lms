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
        $targetDir = public_path('tests');
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Truncate existing records to prevent duplicates on rerun
        DB::table('lms_tests')->truncate();

        $this->command->info("Starting download and seeding of " . count($tests) . " tests...");

        // Stream context to completely bypass SSL verification
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ]);

        foreach ($tests as $index => $item) {
            $filePath = $item['filePath'];
            $fileName = basename($filePath);
            $localPath = "tests/" . $fileName;
            $absoluteLocalPath = public_path($localPath);

            $remoteUrl = "https://lms-vn.cmsedu.net" . $filePath;

            // Determine test type:
            // UCREA if testGradeCdNm has "Lớp" or testTypeCdNm contains "học thử"
            // IG.BH if testGradeCdNm starts with "BH" or "IG"
            $testType = 'IG.BH';
            $grade = $item['testGradeCdNm'] ?? '';
            $typeNm = $item['testTypeCdNm'] ?? '';
            if (str_contains($grade, 'Lớp') || str_contains($typeNm, 'học thử')) {
                $testType = 'UCREA';
            }

            // Download file using file_get_contents with stream context
            $this->command->info("({$index}/" . count($tests) . ") Downloading: {$remoteUrl}");
            $saved = false;
            try {
                $content = @file_get_contents($remoteUrl, false, $context);
                if ($content !== false) {
                    File::put($absoluteLocalPath, $content);
                    $this->command->info("Saved to: {$localPath}");
                    $saved = true;
                } else {
                    $this->command->warn("Failed to download from: {$remoteUrl}");
                }
            } catch (\Exception $e) {
                $this->command->warn("Exception downloading: {$remoteUrl} - Error: " . $e->getMessage());
            }

            DB::table('lms_tests')->insert([
                'test_type' => $testType,
                'test_cd' => $item['testGradeCd'] ?? null,
                'level_cd' => $item['testGradeCdNm'] ?? null,
                'test_seq' => $item['testSeq'] ?? null,
                'name' => $item['evalNm'] ?? 'Unnamed Test',
                'pdf_url' => $remoteUrl,
                'local_pdf_path' => $saved ? "/" . $localPath : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Seeding lms_tests completed successfully.");
    }
}
