<?php

namespace App\Models;

use Exception;
use Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class MediaLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'url',
        'alt_text',
        'caption',
        'description',
        'metadata',
        'folder_id',
        'uploaded_by',
        'is_public',
        'width',
        'height',
        'thumbnails',
    ];

    protected $casts = [
        'metadata' => 'array',
        'thumbnails' => 'array',
        'is_public' => 'boolean',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function uploadFile(UploadedFile $file, $folder = null, $options = [])
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generate unique filename
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = Str::slug($filename) . '_' . uniqid() . '.' . $extension;

        // Determine storage path
        $folderPath = $folder ? "media/{$folder}" : 'media';
        $path = $file->storeAs($folderPath, $filename, 'public');
        $url = Storage::url($path);

        // Get image dimensions if it's an image
        $width = null;
        $height = null;
        $thumbnails = [];

        if (str_starts_with($mimeType, 'image/')) {
            $imagePath = Storage::path('public/' . $path);
            $imageSize = getimagesize($imagePath);

            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];

                // Generate thumbnails
                $thumbnails = static::generateThumbnails($imagePath, $path);
            }
        }

        // Extract metadata
        $metadata = static::extractMetadata($file, $options);

        // Create media record
        $media = static::create([
            'filename' => $filename,
            'original_filename' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'path' => $path,
            'url' => $url,
            'alt_text' => $options['alt_text'] ?? '',
            'caption' => $options['caption'] ?? '',
            'description' => $options['description'] ?? '',
            'metadata' => $metadata,
            'folder_id' => $options['folder_id'] ?? null,
            'uploaded_by' => auth()->id(),
            'is_public' => $options['is_public'] ?? true,
            'width' => $width,
            'height' => $height,
            'thumbnails' => $thumbnails,
        ]);

        return $media;
    }

    protected static function generateThumbnails($imagePath, $originalPath)
    {
        $thumbnails = [];
        $sizes = [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 600],
        ];

        foreach ($sizes as $sizeName => [$maxWidth, $maxHeight]) {
            try {
                $image = Image::make($imagePath);

                // Resize maintaining aspect ratio
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Generate thumbnail filename
                $pathInfo = pathinfo($originalPath);
                $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$sizeName}." . $pathInfo['extension'];
                $thumbnailFullPath = Storage::path('public/' . $thumbnailPath);

                // Save thumbnail
                $image->save($thumbnailFullPath);

                $thumbnails[$sizeName] = [
                    'path' => $thumbnailPath,
                    'url' => Storage::url($thumbnailPath),
                    'width' => $image->width(),
                    'height' => $image->height(),
                ];

            } catch (Exception $e) {
                // Log error but continue
                Log::warning("Failed to generate {$sizeName} thumbnail: " . $e->getMessage());
            }
        }

        return $thumbnails;
    }

    protected static function extractMetadata(UploadedFile $file, $options = [])
    {
        $metadata = [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toISOString(),
        ];

        // Extract EXIF data for images
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $exifData = @exif_read_data($file->getPathname());
                if ($exifData) {
                    $metadata['exif'] = $exifData;
                }
            } catch (Exception $e) {
                // EXIF data not available or corrupted
            }
        }

        return $metadata;
    }

    public function getThumbnailUrl($size = 'thumbnail')
    {
        if (isset($this->thumbnails[$size])) {
            return $this->thumbnails[$size]['url'];
        }

        return $this->url;
    }

    public function getFormattedSize()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isAudio()
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    public function isDocument()
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    public function delete()
    {
        // Delete file from storage
        if (Storage::exists('public/' . $this->path)) {
            Storage::delete('public/' . $this->path);
        }

        // Delete thumbnails
        if ($this->thumbnails) {
            foreach ($this->thumbnails as $thumbnail) {
                if (Storage::exists('public/' . $thumbnail['path'])) {
                    Storage::delete('public/' . $thumbnail['path']);
                }
            }
        }

        return parent::delete();
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ]);
    }

    public function scopeInFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}