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
     * @return array
     */
    public function votesAll(): array
    {
        $votes = [];
        foreach ($this->db->table('talks')->order('created DESC') as $talk) {
            $talkId = $talk->id;
            $votes[$talkId] = $this->db->table(self::TABLE)->where(self::TALK_ID, $talkId)->count();
        }
        return $votes;
    }


    /**
     * @param $participantId
     * @return array
     */
    public function participantVoted($participantId): array
    {
        $p_voted = [];
        foreach ($this->db->table('talks')->order('created DESC') as $row) {
            $p_voted[$row->id] = false;
        }
        $result = $this->db->table(self::TABLE)->where(self::PARTICIPANT_ID, $participantId);
        foreach ($result as $row) {
            $p_voted[$row->talk_id] = true;
        }
        return $p_voted;
    }


    /**
     * @param int $participantId
     * @param int $talkId
     * @return bool
     */
    public function checkIfExist(int $participantId, int $talkId): bool
    {
        $votesCount = $this->db->table(self::TABLE)
            ->where('participant_id', $participantId)
            ->where('talk_id', $talkId)
            ->count();

        return $votesCount > 0;
    }


    /**
     * @param $participantId
     * @return int
     */
    public function clearVotes($participantId): int
    {
        return $this->db->table(self::TABLE)->where('participant_id', $participantId)->delete();
    }


    /**
     * @param int $participantId
     * @param int $talkId
     * @return ActiveRow
     * @throws \Nette\InvalidArgumentException
     */
    public function insert(int $participantId, int $talkId): ActiveRow
    {
        $data = [];
        $data[self::PARTICIPANT_ID] = $participantId;
        $data[self::TALK_ID] = $talkId;
        $this->updateVotes();
        return $this->db->table(self::TABLE)->insert($data);
    }


    /**
     * @return bool
     * @throws \Nette\InvalidArgumentException
     */
    public function updateVotes(): bool
    {
        foreach ($this->db->table('talks')->order('created DESC') as $talk) {
            $talkId = $talk->id;
            $votes = $this->db->table(self::TABLE)->where(self::TALK_ID, $talkId)->count();
            $this->db->table('talks')->where('id', $talkId)->update(['votes' => $votes]);
        }
        return true;
    }
}
