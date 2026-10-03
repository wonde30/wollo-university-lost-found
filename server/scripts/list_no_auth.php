<?php

$data = json_decode(file_get_contents(__DIR__ . '/rbac_detailed_matrix.json'), true);

$noAuthOrPolicy = [];
$publicGuestAuth = [];
$publicDiscovery = [];

foreach ($data as $r) {
    if (!$r['auth_middleware'] && !$r['role_middleware'] && !$r['has_authorize_call']) {
        if (str_starts_with($r['uri'], 'api/v1/auth/')) {
            $publicGuestAuth[] = $r;
        } elseif (str_starts_with($r['uri'], 'api/v1/public/') || str_starts_with($r['uri'], 'api/v1/returns/confirm-token/')) {
            $publicDiscovery[] = $r;
        } else {
            $noAuthOrPolicy[] = $r;
        }
    }
}

echo "=== GUEST AUTH ROUTES (No session required, rate-limited) ===\n";
foreach ($publicGuestAuth as $r) {
    echo "  " . $r['methods'] . " /" . $r['uri'] . " -> " . $r['controller'] . "@" . $r['method'] . "\n";
}

echo "\n=== PUBLIC DISCOVERY ROUTES (Guest accessible) ===\n";
foreach ($publicDiscovery as $r) {
    echo "  " . $r['methods'] . " /" . $r['uri'] . " -> " . $r['controller'] . "@" . $r['method'] . "\n";
}

echo "\n=== UNPROTECTED INTERNAL/BUSINESS ROUTES (SECURITY RISKS) ===\n";
if (empty($noAuthOrPolicy)) {
    echo "  NONE! Every non-public route enforces auth:sanctum + active + role middleware and/or policy authorization.\n";
} else {
    foreach ($noAuthOrPolicy as $r) {
        echo "  " . $r['methods'] . " /" . $r['uri'] . " -> " . $r['controller'] . "@" . $r['method'] . "\n";
    }
}
