<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\Selection;

class Contact
{
    protected const TABLE = 'people';
    protected const CATEGORY = 'category';
    protected const ORDER = 'num_order';

    /**
     * @var Database\Context
     */
    private $db;


    public function __construct(Database\Context $db)
    {
        $this->db = $db;
    }


    public function getOrganisators(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::CATEGORY, 'org')
            ->order(self::ORDER);
    }


    public function getAsistents(): Selection
    {
        return $this->db->table(self::TABLE)
            ->where(self::CATEGORY, 'asist')
            ->order(self::ORDER);
    }
}
