<?php

return [
    'disk_name' => env('MEDIA_DISK', 'public'),
    'max_file_size' => 1024 * 1024 * 50,
    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),
    'queue_name' => env('MEDIA_QUEUE', ''),
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,
    'use_default_collection_name' => 'default',
    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,
    'url_generator' => Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,
    'version_urls' => false,
];

