<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Validates base64/data-URI images by decoding them with GD and writing a
 * re-encoded copy. The original bytes are never stored, so appended PHP or
 * polyglot payloads cannot survive. Shared by question imports and Soalmedia
 * style uploads.
 */
class Question_image
{
    const MAX_FILE_SIZE = 4194304;
    const MAX_DIMENSION = 5000;
    const MAX_PIXELS = 12000000;

    public static function storeEncoded($encoded, $prefix, $directory)
    {
        $encoded = str_replace('%2B', '+', trim((string) $encoded));
        if (stripos($encoded, 'data:') === 0) {
            if (!preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $encoded)) {
                return NULL;
            }
            $encoded = substr($encoded, strpos($encoded, ',') + 1);
        }
        if ($encoded === '' || strlen($encoded) > 6 * 1024 * 1024) {
            return NULL;
        }

        $binary = base64_decode($encoded, TRUE);
        if ($binary === FALSE || $binary === '' || strlen($binary) > self::MAX_FILE_SIZE) {
            return NULL;
        }

        $info = @getimagesizefromstring($binary);
        $allowed = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif');
        if (defined('IMAGETYPE_WEBP')) {
            $allowed[IMAGETYPE_WEBP] = 'webp';
        }
        if (!$info || !isset($allowed[$info[2]])) {
            return NULL;
        }
        if ($info[0] > self::MAX_DIMENSION || $info[1] > self::MAX_DIMENSION
            || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            return NULL;
        }

        $image = @imagecreatefromstring($binary);
        if (!$image) {
            return NULL;
        }

        $directory = rtrim((string) $directory, '/\\');
        if (!is_dir($directory) && !mkdir($directory, 0755, TRUE)) {
            imagedestroy($image);
            return NULL;
        }

        $prefix = preg_replace('/[^a-z0-9]+/i', '_', (string) $prefix);
        $prefix = trim($prefix, '_') === '' ? 'soal' : trim($prefix, '_');
        $type = $info[2];
        $filename = $prefix . '_' . bin2hex(random_bytes(12)) . '.' . $allowed[$type];
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        if ($type === IMAGETYPE_JPEG) {
            $saved = imagejpeg($image, $path, 88);
        } elseif ($type === IMAGETYPE_PNG) {
            imagealphablending($image, FALSE);
            imagesavealpha($image, TRUE);
            $saved = imagepng($image, $path, 6);
        } elseif ($type === IMAGETYPE_GIF) {
            $saved = imagegif($image, $path);
        } else {
            $saved = function_exists('imagewebp') ? imagewebp($image, $path, 88) : FALSE;
        }
        imagedestroy($image);

        return $saved ? $filename : NULL;
    }
}