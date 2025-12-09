<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dates = \App\Models\ExamSchedule::select('exam_date')->distinct()->orderBy('exam_date', 'desc')->get();

echo "Total distinct dates: " . $dates->count() . PHP_EOL;

foreach ($dates as $d) {
    echo $d->exam_date->format('Y-m-d') . ' | ' . $d->exam_date->format('d-m-Y') . PHP_EOL;
}

echo PHP_EOL . "All exam_dates without distinct:" . PHP_EOL;

$all = \App\Models\ExamSchedule::select('exam_date')->orderBy('exam_date', 'desc')->get();

foreach ($all as $d) {
    echo $d->exam_date->format('Y-m-d') . PHP_EOL;
}