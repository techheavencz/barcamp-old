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

    public function votesAll(): array
    {
        $votes = [];
        foreach($this->db->table('talks')->order('created DESC') as $talk) {
            $talkId = $talk->id;
            $votes[$talkId] = $this->db->table(self::TABLE)->where(self::TALK_ID, $talkId)->count();
        }
        return $votes;
    }

    public function checkIfExist(int $participantId, int $talkId): bool
    {
        if($this->db->table(self::TABLE)->where("participant_id", $participantId)->where("talk_id", $talkId)->count() == 0) {
            return false;
        }
        else {
            return true;
        }
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