<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = DB::table('contracts as c')
    ->join('students as s', 'c.student_id', '=', 's.id')
    ->join('classes as cl', 'c.class_id', '=', 'cl.id')
    ->where('cl.class_seq', 6872)
    ->select('s.*')
    ->get();
    
echo json_encode($students);
