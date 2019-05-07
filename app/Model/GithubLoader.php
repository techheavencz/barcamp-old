<?php
declare(strict_types=1);

namespace App\Model;

class GithubLoader
{
    /**
     * @var string
     */
    private $urlPrefix;


    /**
     * @param string $urlPrefix
     */
    public function __construct(string $urlPrefix)
    {
        $this->urlPrefix = $urlPrefix;
    }


    /**
     * @param string $path
     * @return string
     * @throws NotFoundException
     */
    public function load(string $path): string
    {
        $url = $this->urlPrefix . $path . '.html';
        return $this->fetchContent($url);
    }


    /**
     * @param string $url
     * @return string
     * @throws NotFoundException
     */
    protected function fetchContent(string $url): string
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, 'plzenskybarcamp.cz/archive');

        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode === 404) {
            throw new NotFoundException('GitHub content does not exists: ' . $url, 404);
        }

        $content = curl_exec($curl);
        curl_close($curl);

        return $content;
    }
}
