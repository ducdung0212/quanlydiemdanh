<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ExamSchedule;

$date = '2025-11-23';
$items = ExamSchedule::whereDate('exam_date', $date)->get();

$result = $items->map(function($it){
    return [
        'id' => $it->id,
        'exam_date_raw' => $it->getAttributes()['exam_date'] ?? null,
        'exam_date_attr' => $it->exam_date ? $it->exam_date->format('Y-m-d') : null,
        'exam_date_json' => $it->toArray()['exam_date'] ?? null,
    ];
});

echo json_encode($result->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;