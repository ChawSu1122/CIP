<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RegistrationStorageMetric;

$row = RegistrationStorageMetric::find(2);
if ($row) {
    $row->total_bytes = $row->user_row_bytes + $row->session_row_bytes + $row->token_bytes;
    $row->total_kb = round($row->total_bytes / 1024, 4);
    $row->save();
    echo "updated row 2: total_kb=" . $row->total_kb . "\n";
} else {
    echo "row 2 not found\n";
}
