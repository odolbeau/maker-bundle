<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\MakerBundle;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Ryan Weaver <weaverryan@gmail.com>
 *
 * @internal
 */
final class FileManager
{
    private $fs;
    private $rootDirectory;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(Filesystem $fs, string $rootDirectory)
    {
        $this->fs = $fs;
        $this->rootDirectory = $rootDirectory;
    }

    public function setIO(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function parseTemplate(string $templatePath, array $parameters): string
    {
        ob_start();
        extract($parameters, EXTR_SKIP);
        include $templatePath;

        return ob_get_clean();
    }

    public function dumpFile(string $filename, string $content)
    {
        $this->fs->dumpFile($this->absolutizePath($filename), $content);
        $this->io->comment(sprintf('<fg=green>created</>: %s', $this->relativizePath($filename)));
    }

    public function fileExists($path): bool
    {
        return file_exists($this->absolutizePath($path));
    }

    public function relativizePath($absolutePath): string
    {
        $relativePath = str_replace($this->rootDirectory, '.', $absolutePath);

        return is_dir($absolutePath) ? rtrim($relativePath, '/').'/' : $relativePath;
    }

    public function createFinder(string $in)
    {
        $finder = new Finder();
        $finder->in($this->absolutizePath($in));

        return $finder;
    }

    private function absolutizePath($path): string
    {
        if (0 === strpos($path, '/')) {
            return $path;
        }

        return sprintf('%s/%s', $this->rootDirectory, $path);
    }
}
