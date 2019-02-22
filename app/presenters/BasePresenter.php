<?php

namespace App\Presenters;

use Nette;


class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    public $db;


    /**
     * @param Nette\Database\Context $database
     */
    public function injectDatabase(Nette\Database\Context $database): void
    {
        $this->db = $database;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $parameters = $this->context->getParameters();
        $this->template->wwwDir = $parameters['wwwDir'];
    }
}
