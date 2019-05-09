<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
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
     * @var FeatureToggle
     */
    private $featureToggle;


    /**
     * @param Database\Context $db
     * @param FeatureToggleFactory $featureToggleFactory
     */
    public function __construct(Database\Context $db, FeatureToggleFactory $featureToggleFactory)
    {
        $this->db = $db;
        $this->featureToggle = $featureToggleFactory->create('talk');
    }


    /**
     * @param string $guid
     * @return ActiveRow
     * @throws NotFoundException
     */
    public function getByGuid(string $guid): ActiveRow
    {
        $talk = $this->db->table(self::TABLE)->where('guid', $guid)->fetch();

        if ($talk === null) {
            throw new NotFoundException('Not found Talk with GUID: ' . $guid);
        }

        return $talk;
    }


    public function isAllowedShowMovies(): bool
    {
        return $this->featureToggle->isAllowed('movies');
    }


    public function isAllowedShowSlides(): bool
    {
        return $this->featureToggle->isAllowed('slides');
    }


    public function getSlides(ActiveRow $talk): array
    {
        try {
            $slides = Json::decode((string)$talk['slidesUrl']);
            return (array)$slides;
        } catch (JsonException $e) {
            return [];
        }
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
     * @throws InvalidArgumentException
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
