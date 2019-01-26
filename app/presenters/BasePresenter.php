<?php

namespace App\Presenters;

use Nette;


class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    public $db;

    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }
}
