<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use ReflectionClass;
use ReflectionMethod;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AuditServicePattern extends BaseCommand
{
    protected $group = 'Dev';
    protected $name = 'service:audit';
    protected $description = 'Audit service methods that need parameter and return type hints.';

    public function run(array $params)
    {
        $path = APPPATH . 'Services';

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($path . DIRECTORY_SEPARATOR, '', $file->getPathname());

            $class = 'App\\Services\\'
                . str_replace(
                    ['/', '\\', '.php'],
                    ['\\', '\\', ''],
                    $relativePath
                );

            if (! class_exists($class)) {
                require_once $file->getPathname();
            }

            if (! class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            CLI::write("\n" . $ref->getName(), 'yellow');

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $method) {
                if ($method->class !== $class) {
                    continue;
                }

                if (str_starts_with($method->getName(), '__')) {
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
                    $visibility = $method->isProtected() ? 'protected' : 'public';

                    CLI::write(
                        '  - ' . $visibility . ' ' . $method->getName() . '(): ' . implode(', ', $issues),
                        'red'
                    );
                }
            }
        }

        CLI::write("\nAudit service selesai.", 'green');
    }
}
