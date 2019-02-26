<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\ActiveRow;

class Talk
{
    protected const TABLE = 'talks';
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


    public function insert(array $data, int $participantId): ActiveRow
    {
        $data[self::PARTICIPANT_ID] = $participantId;

        return $this->db->table(self::TABLE)->insert($data);
    }
}
