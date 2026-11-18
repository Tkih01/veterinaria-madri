<?php
declare(strict_types=1);

namespace App\Config;

use RuntimeException;

/**
 * Procesador de imágenes: valida MIME real, limpia metadatos EXIF/GPS (LFPDPPP),
 * redimensiona y guarda en formato .webp eliminando el archivo anterior si se reemplaza.
 */
final class ImageUploader
{
    private const MAX_BYTES = 2097152; // 2 MB
    private const MAX_DIM   = 800;     // 800px máximo ancho/alto

    public static function uploadAndConvertToWebp(
        array $file,
        string $subDir,
        string $prefix,
        ?string $oldFilename = null
    ): ?string {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldFilename;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al recibir el archivo de imagen.');
        }

        if ($file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('La imagen supera el peso máximo permitido de 2 MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Formato no válido. Solo se permiten imágenes JPG, PNG o WebP.');
        }

        $srcImg = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default      => false,
        };

        if (!$srcImg) {
            throw new RuntimeException('No fue posible procesar la imagen enviada.');
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);

        $ratio = min(self::MAX_DIM / $origW, self::MAX_DIM / $origH, 1.0);
        $newW  = max(1, (int) round($origW * $ratio));
        $newH  = max(1, (int) round($origH * $ratio));

        $dstImg = imagecreatetruecolor($newW, $newH);
        imagealphablending($dstImg, true);
        imagesavealpha($dstImg, true);

        $bg = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $bg);

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $targetDir = BASE_PATH . '/public/assets/img/' . trim($subDir, '/') . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0750, true);
        }

        $newFilename = strtolower($prefix) . '-' . bin2hex(random_bytes(6)) . '.webp';
        $fullPath    = $targetDir . $newFilename;

        if (!imagewebp($dstImg, $fullPath, 82)) {
            imagedestroy($srcImg);
            imagedestroy($dstImg);
            throw new RuntimeException('No se pudo guardar la imagen optimizada en el servidor.');
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        // Si había una foto previa, eliminarla del disco para no dejar basura
        if (!empty($oldFilename)) {
            self::deleteFile($subDir, $oldFilename);
        }

        return $newFilename;
    }

    public static function deleteFile(string $subDir, ?string $filename): void
    {
        if (empty($filename)) {
            return;
        }
        $safeName = basename($filename);
        $filePath = BASE_PATH . '/public/assets/img/' . trim($subDir, '/') . '/' . $safeName;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
}