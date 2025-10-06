<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$sql = "select constraint_name, table_name, column_name, referenced_table_name, referenced_column_name from information_schema.key_column_usage where constraint_schema = database() and referenced_table_name is not null";
$rows = \DB::select($sql);
print_r($rows);

$sql2 = "select table_name, engine from information_schema.tables where table_schema = database() and table_name in ('users','students','faculties','lecturers','exam_schedules','exam_supervisors','attendance_records','subjects')";
$rows2 = \DB::select($sql2);
print_r($rows2);
