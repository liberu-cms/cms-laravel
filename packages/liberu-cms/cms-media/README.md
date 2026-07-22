# cms-media

The media library for Liberu CMS: secure uploads, storage, folders, and
metadata, exposed to the rest of the platform behind a content-agnostic contract.

## Public contracts (in `cms-contracts`)

| Contract | Purpose |
|----------|---------|
| `MediaRepositoryInterface` | `find` / `inFolder` / `delete`. How other modules resolve and manage media by key. |
| `MediaItemInterface` | A read-only media value: disk, path, url, file name, mime type, size, folder, metadata. |
| `MediaUploaded` (event) | Broadcast after a file is stored — for image processing, indexing, CDN warmers. |

Content modules store a media **key** and resolve it through the repository; they
never touch the media model, disk, or storage backend.

## Uploading

`StoreUpload` (a module service) validates and stores an `UploadedFile`:

```php
$media = app(\Liberu\Cms\Media\Media\StoreUpload::class)($request->file('file'), folder: 'articles');
$media->url();
```

- **Secure by default (OWASP A08):** MIME type is derived from file *contents*
  (not the client's claim) and checked against an allow-list; size is bounded.
  Violations throw `InvalidUpload`.
- Image uploads capture `width`/`height` metadata automatically.

## Config (`config/cms-media.php`)

| Key | Default | Purpose |
|-----|---------|---------|
| `disk` | `public` | Storage disk for uploads. |
| `max_size_kb` | `20480` | Maximum upload size. |
| `allowed_mime_types` | image/video/audio/doc set | Accepted content types. |

## Events

- **Emits:** `Liberu\Cms\Contracts\Events\Media\MediaUploaded`.
- **Listens:** none.

## Extension points / roadmap

- **Image processing** (resize, thumbnails, WebP): a `MediaUploaded` listener is
  the intended seam. Full processing needs an imaging library
  (e.g. `intervention/image`) — a dependency to add with approval.
- **CDN / remote disks** (S3, R2, Spaces): configure the `disk`.
