<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\ActiveRow;

class Voting
{
    protected const TABLE = 'voting';
    protected const PARTICIPANT_ID = 'participant_id';
    protected const TALK_ID = 'talk_id';
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
     * @param int $talkId
     * @return int
     */
    public function votes(int $talkId): int
    {
        return $this->db->table(self::TABLE)->where(self::TALK_ID, $talkId)->count();
    }


    /**
     * @param int $participantId
     * @param int $talkId
     * @return ActiveRow
     */
    public function insert(int $participantId, int $talkId): ActiveRow
    {
        $data = [];
        $data[self::PARTICIPANT_ID] = $participantId;
        $data[self::TALK_ID] = $talkId;

        return $this->db->table(self::TABLE)->insert($data);
    }
}
