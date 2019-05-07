<?php
declare(strict_types=1);

namespace App\Model;

class FeatureToggle
{
    /**
     * @var string
     */
    private $group;
    /**
     * @var Config
     */
    private $config;


    /**
     * @param string $group
     * @param Config $config
     */
    public function __construct(string $group, Config $config)
    {
        $this->group = $group;
        $this->config = $config;
    }


    /**
     * @param string $id
     * @return bool
     */
    public function isAllowed(string $id): bool
    {
        $key = sprintf('feature.%s.%s', $this->group, $id);
        return (bool)$this->config->getById($key, false);
    }
}
