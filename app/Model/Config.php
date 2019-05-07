<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;
use Tracy\Logger;

/**
 * Class Config
 * @package App\Model
 */
class Config
{
    protected const TABLE = 'config';
    /**
     * @var Database\Context
     */
    private $db;

    /**
     * @var array
     */
    private $cache;


    /**
     * @param Database\Context $db
     */
    public function __construct(Database\Context $db)
    {
        $this->db = $db;
    }


    /**
     * @param string $id
     * @param null $default
     * @return mixed|null
     */
    public function getById(string $id, $default = null)
    {
        $data = $this->getData();

        if (isset($data[$id])) {
            try {
                return Json::decode($data[$id]);
            } catch (JsonException $e) {
                Debugger::log($e, Logger::ERROR);
                return $default;
            }
        }

        return $default;
    }


    /**
     * @return array
     */
    protected function getData(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        return $this->cache = $this->db->table(self::TABLE)->fetchPairs('id', 'value');
    }
}
