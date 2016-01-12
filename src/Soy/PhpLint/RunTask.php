<?php

namespace Soy\PhpLint;

use League\CLImate\CLImate;
use Soy\Task\TaskInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RunTask implements TaskInterface
{
    /**
     * @var CLImate
     */
    protected $climate;

    /**
     * @var string
     */
    protected $binary = 'php';

    /**
     * @var bool
     */
    protected $verbose = false;

    /**
     * @var string
     */
    protected $cacheFile;

    /**
     * @param CLImate $climate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    /**
     * @param Finder|null $finder
     */
    public function run(Finder $finder = null)
    {
        if (!$finder instanceof Finder) {
            if ($this->isVerbose()) {
                $this->climate->dim('Falling back to default finder');
            }
            $finder = $this->createDefaultFinder();
        }

        $cache = false;

        if (!$this->isCacheEnabled()) {
            if ($this->isVerbose()) {
                $this->climate->dim('Running PHP Lint without caching');
            }
        } else {
            $cache = $this->getCache();
        }

        /** @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $path = $file->getPathname();
            $modifiedTime = filemtime($path);

            if ($this->isCacheEnabled() && array_key_exists($path, $cache) && $cache[$path] === $modifiedTime) {
                if ($this->isVerbose()) {
                    $this->climate->dim('Skipping ' . $path);
                }
                continue;
            }

            $this->executeCommand($path);

            if ($this->isCacheEnabled()) {
                $cache[$path] = $modifiedTime;
            }
        }

        if ($this->isCacheEnabled()) {
            $this->setCache($cache);
            if ($this->isVerbose()) {
                $this->climate->dim('Cache written to file ' . $this->cacheFile);
            }
        }
    }

    /**
     * @return string
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * @param string $binary
     * @return $this
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
        return $this;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getCommand($file)
    {
        return $this->getBinary() . ' -l ' . escapeshellarg($file);
    }

    /**
     * @param string $cacheFile
     * @return $this
     */
    public function enableCache($cacheFile)
    {
        $this->cacheFile = $cacheFile;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        unset($this->cacheFile);
        return $this;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return is_string($this->cacheFile);
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param bool $verbose
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
        return $this;
    }

    /**
     * @return array
     */
    protected function getCache()
    {
        $cache = [];

        if (is_file($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);

            if (!is_array($cache)) {
                $cache = [];
            }
        }

        return $cache;
    }

    /**
     * @param array $cache
     */
    protected function setCache(array $cache)
    {
        file_put_contents($this->cacheFile, json_encode($cache));
    }

    /**
     * @return Finder
     */
    protected function createDefaultFinder()
    {
        return Finder::create()->in('.')->ignoreVCS(true)->name('*.php');
    }

    /**
     * @param string $path
     */
    protected function executeCommand($path)
    {
        $command = $this->getCommand($path);

        if ($this->isVerbose()) {
            $this->climate->lightBlue('$ ' . $command);
        }

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new PhpLintException(
                'PHP Lint failed for file ' . $path . ': ' . PHP_EOL . implode(PHP_EOL, $output),
                $exitCode
            );
        }
    }
}
