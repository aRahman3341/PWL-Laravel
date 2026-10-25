<?php

return [
    'url'       => rtrim(env('SUPABASE_URL', ''), '/'),
    // Service key: HANYA di server. Jangan pernah ditaruh di JavaScript/browser.
    'key'       => env('SUPABASE_SERVICE_KEY', ''),
    // Anon / publishable key: dipakai hanya untuk proses login pegawai.
    'anon_key'  => env('SUPABASE_ANON_KEY', ''),
    'cache_ttl' => (int) env('SUPABASE_CACHE_TTL', 300),
    'bucket'    => 'confidential',
];
