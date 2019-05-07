<?php

namespace App\Model;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class ArchiveLoader
{

    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var GithubLoader
     */
    private $githubLoader;


    /**
     * @param GithubLoader $githubLoader
     * @param IStorage $cacheStorage
     */
    public function __construct(GithubLoader $githubLoader, IStorage $cacheStorage)
    {
        $this->cache = new Cache($cacheStorage, 'archive-loader');
        $this->githubLoader = $githubLoader;
    }


    /**
     * @param $path
     * @return array
     */
    public function load($path): array
    {
        /** @var array $content */
        $content = $this->cache->load($path, function (& $cacheParams) use ($path) {
            $cacheParams = [Cache::EXPIRE => '1 month'];
            return $this->loadArchiveStorage($path);
        });

        return $content;
    }


    /**
     * @param $path
     * @return array
     */
    private function loadArchiveStorage($path): array
    {
        try {
            $content = $this->githubLoader->load($path);
            return [
                'status' => 200,
                'content' => $content,
            ];
        } catch (NotFoundException $e) {
            return [
                'status' => 404,
                'content' => null,
            ];
        }
    }
}