<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;

class DownloadLmsTestPdfs extends Command
{
    protected $signature = 'lms:download-test-pdfs';
    protected $description = 'Download large PDF files from lms_tests table and clean up unused files.';

    public function handle()
    {
        $tests = DB::table('lms_tests')->whereNotNull('pdf_url')->get();
        $totalCount = $tests->count();
        
        $targetDir = public_path('test_files');
        
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $validFiles = [];
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar();
        $client = new Client(['cookies' => $cookieJar, 'verify' => false]);

        $this->info("=======================================");
        $this->info("Bắt đầu xử lý tổng cộng $totalCount files.");
        $this->info("Thư mục lưu trữ: $targetDir");
        $this->info("=======================================\n");

        $this->info("=> Đang thực hiện đăng nhập để lấy Cookie...");
        try {
            $client->request('POST', 'https://lms-vn.cmsedu.net/login/ajaxLogin.do', [
                'form_params' => [
                    'membId' => 'cms.vn',
                    'membPasswd' => 'hpa@XuanDieu'
                ]
            ]);
            $this->info("=> Đăng nhập thành công, đã lưu Cookie.\n");
        } catch (\Exception $e) {
            $this->error("=> Lỗi đăng nhập: " . $e->getMessage());
        }

        foreach ($tests as $index => $test) {
            $currentStep = $index + 1;
            $url = $test->pdf_url;
            
            $this->info("[$currentStep/$totalCount] Đang kiểm tra record ID {$test->id}...");
            $this->info("   - URL gốc: $url");

            if (empty($url)) {
                $this->warn("   -> URL trống, bỏ qua.");
                continue;
            }

            if (!str_starts_with($url, 'http')) {
                $url = 'https://lms-vn.cmsedu.net' . (str_starts_with($url, '/') ? '' : '/') . $url;
            }

            $fileName = basename(parse_url($url, PHP_URL_PATH));
            $actualDownloadUrl = $url;

            // Xử lý các link previewPaper
            if (strpos($url, 'previewPaper.do') !== false) {
                $this->info("   -> Phát hiện link preview HTML, đang trích xuất link PDF gốc...");
                try {
                    $response = $client->request('POST', $url);
                    $html = (string) $response->getBody();
                    
                    if (preg_match('/file=([^"&\']+)/', $html, $matches)) {
                        $actualDownloadUrl = $matches[1];
                        if (!str_starts_with($actualDownloadUrl, 'http')) {
                            $actualDownloadUrl = 'https://lms-vn.cmsedu.net' . (str_starts_with($actualDownloadUrl, '/') ? '' : '/') . $actualDownloadUrl;
                        }
                        $fileName = basename(parse_url($actualDownloadUrl, PHP_URL_PATH));
                        $this->info("   -> Đã trích xuất thành công link thật: $actualDownloadUrl");
                    } else {
                        $this->error("   -> Không tìm thấy link PDF trong nội dung HTML, bỏ qua.");
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->error("   -> Lỗi khi truy cập trang preview: " . $e->getMessage());
                    continue;
                }
            }

            $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
            $localPath = $targetDir . '/' . $fileName;
            $relativePath = 'test_files/' . $fileName;
            $validFiles[] = $fileName;

            // Xóa file nếu trước đó bị tải nhầm thành HTML
            if (File::exists($localPath) && filesize($localPath) < 2048) {
                $content = file_get_contents($localPath);
                if (strpos($content, '<!DOCTYPE HTML>') !== false || strpos($content, '<html') !== false) {
                    $this->warn("   -> Phát hiện file rác (HTML) cũ, tiến hành xóa: $fileName");
                    File::delete($localPath);
                }
            }

            // Kiểm tra file đã có chưa
            if (File::exists($localPath)) {
                $sizeMB = round(filesize($localPath) / 1024 / 1024, 2);
                $this->info("   -> File đã tồn tại ($sizeMB MB), bỏ qua tải mới.");
                DB::table('lms_tests')->where('id', $test->id)->update(['local_pdf_path' => $relativePath]);
                $this->info("---------------------------------------");
                continue;
            }

            // Tiến hành tải
            $this->info("   -> Bắt đầu tải file: $actualDownloadUrl");
            $startTime = microtime(true);
            try {
                $client->request('GET', $actualDownloadUrl, ['sink' => $localPath, 'timeout' => 600]);
                DB::table('lms_tests')->where('id', $test->id)->update(['local_pdf_path' => $relativePath]);
                
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 1);
                $sizeMB = round(filesize($localPath) / 1024 / 1024, 2);
                
                $this->info("   -> Tải XONG: $fileName (Dung lượng: $sizeMB MB - Thời gian: {$duration}s)");
            } catch (\Exception $e) {
                $this->error("   -> Tải THẤT BẠI: " . $e->getMessage());
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
            $this->info("---------------------------------------");
        }

        $this->info("\n=======================================");
        $this->info("Bắt đầu dọn dẹp các file rác trong /public/test_files...");
        $allFiles = File::files($targetDir);
        $deletedCount = 0;
        foreach ($allFiles as $file) {
            if (!in_array($file->getFilename(), $validFiles)) {
                $this->warn(" -> Xóa file thừa: " . $file->getFilename());
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }
        $this->info("Đã xóa $deletedCount file rác.");
        $this->info("HOÀN TẤT TOÀN BỘ TIẾN TRÌNH!");
        $this->info("=======================================\n");
    }
}
