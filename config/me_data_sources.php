<?php

$localRoot = trim((string) env('ME_DATA_SOURCE_LOCAL_ROOT', ''));

return [
    /* Exact host names only. An empty list disables all remote synchronization. */
    'remote_allowed_hosts' => array_values(array_filter(array_map(
        fn (string $host) => strtolower(rtrim(trim($host), '.')),
        preg_split('/[\s,]+/', (string) env('ME_DATA_SOURCE_REMOTE_ALLOWED_HOSTS', '')) ?: []
    ))),

    'remote_max_bytes' => (int) env('ME_DATA_SOURCE_REMOTE_MAX_BYTES', 10 * 1024 * 1024),

    /* Persisted file sources must resolve canonically beneath this directory. */
    'local_root' => $localRoot !== ''
        ? $localRoot
        : storage_path('app/private/me-data-source-imports'),
];
