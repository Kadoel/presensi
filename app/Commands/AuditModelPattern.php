<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

class AuditModelPattern extends BaseCommand
{
    protected $group = 'Dev';
    protected $name = 'model:audit';
    protected $description = 'Audit model methods that need parameter and return type hints.';

    public function run(array $params)
    {
        $path = APPPATH . 'Models';

        foreach (glob($path . '/*.php') as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class)) {
                require_once $file;
            }

            if (! class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            CLI::write("\n" . $ref->getShortName(), 'yellow');

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->class !== $class) {
                    continue;
                }

                $issues = [];

                foreach ($method->getParameters() as $param) {
                    if (! $param->hasType()) {
                        $issues[] = '$' . $param->getName() . ' no type';
                    }
                }

                if (! $method->hasReturnType()) {
                    $issues[] = 'no return type';
                }

                if (! empty($issues)) {
                    CLI::write('  - ' . $method->getName() . '(): ' . implode(', ', $issues), 'red');
                }
            }
        }

        CLI::write("\nAudit selesai.", 'green');
    }
}
