<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Random;

class Talk
{
    protected const TABLE = 'talks';
    protected const GUID = 'guid';
    protected const PARTICIPANT_ID = 'participant_id';
    /**
     * @var Database\Context
     */
    private $db;


    /**
     * @param Database\Context $db
     */
    public function __construct(Database\Context $db)
    {
        $this->db = $db;
    }


    /**
     * @param string $guid
     * @return ActiveRow
     * @throws NotFoundException
     */
    public function getByGuid(string $guid): ActiveRow
    {
        $talk = $this->db->table(self::TABLE)->where('guid', $guid)->fetch();

        if ($talk instanceof ActiveRow === false) {
            throw new NotFoundException('Not found Talk with GUID: ' . $guid);
        }

        return $talk;
    }


    /**
     * @return Database\Table\Selection
     */
    public function find(): Database\Table\Selection
    {
        return $this->db->table(self::TABLE)->order('votes DESC');
    }


    /**
     * @param array $data
     * @param int $participantId
     * @return ActiveRow
     * @throws \Nette\InvalidArgumentException
     */
    public function insert(array $data, int $participantId): ActiveRow
    {
        $data[self::PARTICIPANT_ID] = $participantId;
        if (isset($data[self::GUID]) === false) {
            $data[self::GUID] = Random::generate(8);
        }

        return $this->db->table(self::TABLE)->insert($data);
    }
}
