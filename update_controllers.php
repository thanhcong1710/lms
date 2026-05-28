<?php

$controllers = [
    'BranchController.php' => 'Branch',
    'TeacherController.php' => 'Teacher',
    'ClassController.php' => 'Classes', // Model is Classes or ClassModel? Let's check
    'StudentController.php' => 'Student',
    'ContractController.php' => 'Contract',
];

foreach ($controllers as $file => $model) {
    $path = "/Users/mac24h/Documents/docker-work/src/lms/app/Http/Controllers/$file";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $newIndex = <<<EOT
    public function index(Request \$request)
    {
        \$limit = \$request->query('per_page', 20);
        if (!in_array(\$limit, [20, 50, 100])) {
            \$limit = 20;
        }
        return response()->json(\$model::paginate(\$limit));
    }
EOT;
        // Wait, the model variable needs to be literal in the code
        $newIndex = str_replace("\$model", $model, $newIndex);
        
        // Find existing index method
        $pattern = '/public function index\(\).*?\{.*?\}/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $newIndex, $content);
            file_put_contents($path, $content);
        }
    }
}
echo "Done";
