<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class FileService
{
    /**
     * Resolve and validate that target path is safely inside website root directory.
     */
    public function resolveRealPath(Website $website, string $relativeSubpath = ''): ?string
    {
        $baseRoot = realpath($website->document_root);

        if (! $baseRoot) {
            // Fallback if directory not created yet
            if (File::exists($website->document_root)) {
                $baseRoot = $website->document_root;
            } else {
                File::makeDirectory($website->document_root, 0755, true, true);
                $baseRoot = realpath($website->document_root);
            }
        }

        $cleanRelative = ltrim(str_replace(['../', '..\\'], '', $relativeSubpath), '/\\');
        $targetPath = $cleanRelative === '' ? $baseRoot : $baseRoot . DIRECTORY_SEPARATOR . $cleanRelative;

        // Directory traversal security check
        if (str_starts_with(realpath($targetPath) ?: $targetPath, $baseRoot)) {
            return $targetPath;
        }

        return null;
    }

    /**
     * List items inside a directory.
     */
    public function listDirectory(Website $website, string $relativeSubpath = ''): array
    {
        $targetDir = $this->resolveRealPath($website, $relativeSubpath);

        if (! $targetDir || ! File::isDirectory($targetDir)) {
            return [
                'current_path' => $relativeSubpath,
                'items' => [],
                'error' => 'Direktori tidak ditemukan atau akses ditolak.',
            ];
        }

        $files = File::files($targetDir);
        $directories = File::directories($targetDir);

        $items = [];

        foreach ($directories as $dir) {
            $name = basename($dir);
            $items[] = [
                'name' => $name,
                'type' => 'directory',
                'is_dir' => true,
                'size' => '-',
                'modified_at' => date('Y-m-d H:i:s', File::lastModified($dir)),
                'relative_path' => trim($relativeSubpath . '/' . $name, '/'),
            ];
        }

        foreach ($files as $file) {
            $name = $file->getFilename();
            $ext = strtolower($file->getExtension());
            $items[] = [
                'name' => $name,
                'type' => $ext ?: 'file',
                'is_dir' => false,
                'is_zip' => $ext === 'zip',
                'size' => $this->formatSize($file->getSize()),
                'bytes' => $file->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'relative_path' => trim($relativeSubpath . '/' . $name, '/'),
            ];
        }

        return [
            'current_path' => trim($relativeSubpath, '/'),
            'items' => $items,
        ];
    }

    /**
     * Create a new file in directory.
     */
    public function createFile(Website $website, string $relativeSubpath, string $filename, string $content = ''): bool
    {
        $targetPath = $this->resolveRealPath($website, trim($relativeSubpath . '/' . $filename, '/'));

        if (! $targetPath) {
            return false;
        }

        File::put($targetPath, $content);
        $this->chownToSystemUser($website, $targetPath);

        AuditLogger::log('file_created', "File {$filename} dibuat pada website {$website->domain_name}.", $website->user_id);

        return true;
    }

    /**
     * Create a new folder.
     */
    public function createFolder(Website $website, string $relativeSubpath, string $foldername): bool
    {
        $targetPath = $this->resolveRealPath($website, trim($relativeSubpath . '/' . $foldername, '/'));

        if (! $targetPath) {
            return false;
        }

        File::makeDirectory($targetPath, 0755, true, true);
        $this->chownToSystemUser($website, $targetPath);

        AuditLogger::log('folder_created', "Folder {$foldername} dibuat pada website {$website->domain_name}.", $website->user_id);

        return true;
    }

    /**
     * Upload files to target directory.
     */
    public function uploadFile(Website $website, string $relativeSubpath, UploadedFile $file): bool
    {
        $targetDir = $this->resolveRealPath($website, $relativeSubpath);

        if (! $targetDir || ! File::isDirectory($targetDir)) {
            return false;
        }

        $filename = $file->getClientOriginalName();
        $file->move($targetDir, $filename);
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        $this->chownToSystemUser($website, $targetPath);

        AuditLogger::log('file_uploaded', "File {$filename} diunggah ke website {$website->domain_name}.", $website->user_id);

        return true;
    }

    /**
     * Read content of a text file.
     */
    public function getFileContent(Website $website, string $relativeFilePath): ?string
    {
        $targetPath = $this->resolveRealPath($website, $relativeFilePath);

        if (! $targetPath || ! File::isFile($targetPath)) {
            return null;
        }

        return File::get($targetPath);
    }

    /**
     * Save updated content to a file.
     */
    public function saveFileContent(Website $website, string $relativeFilePath, string $content): bool
    {
        $targetPath = $this->resolveRealPath($website, $relativeFilePath);

        if (! $targetPath) {
            return false;
        }

        File::put($targetPath, $content);
        $this->chownToSystemUser($website, $targetPath);

        AuditLogger::log('file_updated', "File {$relativeFilePath} diperbarui pada website {$website->domain_name}.", $website->user_id);

        return true;
    }

    /**
     * Delete a file or directory.
     */
    public function deleteItem(Website $website, string $relativeItemPath): bool
    {
        $targetPath = $this->resolveRealPath($website, $relativeItemPath);

        if (! $targetPath || ! File::exists($targetPath)) {
            return false;
        }

        if (File::isDirectory($targetPath)) {
            File::deleteDirectory($targetPath);
        } else {
            File::delete($targetPath);
        }

        AuditLogger::log('file_deleted', "Item {$relativeItemPath} dihapus pada website {$website->domain_name}.", $website->user_id);

        return true;
    }

    /**
     * Extract a ZIP archive inside document root.
     */
    public function extractZip(Website $website, string $relativeZipPath): bool
    {
        $zipPath = $this->resolveRealPath($website, $relativeZipPath);

        if (! $zipPath || ! File::isFile($zipPath)) {
            return false;
        }

        $extractToDir = dirname($zipPath);

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($extractToDir);
                $zip->close();
                $this->chownToSystemUser($website, $extractToDir);

                AuditLogger::log('zip_extracted', "Arsip ZIP {$relativeZipPath} diekstrak pada website {$website->domain_name}.", $website->user_id);

                return true;
            }
        }

        return false;
    }

    /**
     * Fix ownership to system user & www-data group on Linux with 755 dirs and 644 files.
     */
    public function chownToSystemUser(Website $website, string $path): void
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $sysUser = $website->system_user;
            $vhostDir = dirname($website->document_root); // /var/www/vhosts/site_xxx

            @shell_exec("sudo /bin/chown -R {$sysUser}:www-data " . escapeshellarg($vhostDir) . " 2>&1");
            @shell_exec("sudo /usr/bin/find " . escapeshellarg($vhostDir) . " -type d -exec chmod 755 {} + 2>&1");
            @shell_exec("sudo /usr/bin/find " . escapeshellarg($vhostDir) . " -type f -exec chmod 644 {} + 2>&1");
            
            // Restart PHP-FPM pool if socket crashed
            $phpVer = $website->php_version;
            @shell_exec("sudo /usr/bin/systemctl reload php{$phpVer}-fpm 2>&1");
        }
    }

    /**
     * Helper to format bytes into readable KB / MB.
     */
    protected function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
