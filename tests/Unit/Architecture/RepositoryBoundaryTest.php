<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class RepositoryBoundaryTest extends TestCase
{
    /** @var string[] */
    private $modularRoots = array('src');

    #[Test]
    public function modular_php_files_must_use_strict_types(): void
    {
        foreach ($this->modularPhpFiles() as $path) {
            $content = (string) file_get_contents($path);

            $this->assertMatchesRegularExpression(
                '/^<\\?php\\s+declare\\(strict_types=1\\);/s',
                $content,
                sprintf('Missing strict_types declaration in %s', $path)
            );
        }
    }

    #[Test]
    public function db_access_is_forbidden_outside_repositories_in_modular_layers(): void
    {
        foreach ($this->modularPhpFiles() as $path) {
            if ($this->isRepositoryPath($path)) {
                continue;
            }

            $content = (string) file_get_contents($path);

            $this->assertStringNotContainsString(
                'use Illuminate\\Support\\Facades\\DB;',
                $content,
                sprintf('DB facade import is not allowed outside repositories: %s', $path)
            );

            $this->assertStringNotContainsString(
                'DB::',
                $content,
                sprintf('DB facade usage is not allowed outside repositories: %s', $path)
            );

            $this->assertDoesNotMatchRegularExpression(
                '/use\\s+App\\\\Models\\\\/m',
                $content,
                sprintf('Model imports are not allowed outside repositories: %s', $path)
            );
        }
    }

    /**
     * @return string[]
     */
    private function modularPhpFiles(): array
    {
        $files = array();

        foreach ($this->modularRoots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $files[] = str_replace('\\', '/', $file->getPathname());
            }
        }

        sort($files);

        return $files;
    }

    private function isRepositoryPath(string $path): bool
    {
        return strpos($path, '/Repositories/') !== false
            || strpos($path, '/Shared/Persistence/') !== false;
    }
}
