<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Class FeatureToggleFactory
 * @package App\Model
 */
class FeatureToggleFactory
{
    /**
     * @var Config
     */
    private $config;


    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

    }


    /**
     * @param $group
     * @return FeatureToggle
     */
    public function create($group): FeatureToggle
    {
        return new FeatureToggle($group, $this->config);
    }
}

