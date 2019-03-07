<?php

namespace App\Presenters;


use App\Model\Talk;

final class ConferencePresenter extends BasePresenter
{
    /**
     * @var Talk
     */
    private $talkModel;


    public function __construct(Talk $talkModel)
    {
        parent::__construct();
        $this->talkModel = $talkModel;
    }


    public function renderProfil()
    {
        //TODO Zobrazení detailu profilu

    }


    public function renderTalks()
    {
        $this->template->talks = $this->talkModel->find();
    }

    public function renderTalksVoting()
    {
        $this->template->talks = $this->talkModel->find();
    }



    public function renderTalksDetail()
    {
        //TODO zobrazení detailu

    }


    public function renderVisitors()
    {

        //TODO Zobrazení návštěvníků, co chtějí být zobrazení
        /*
         *  Jsem debil a zapomněl jsem tam dát políčko, jestli chtějí být zobrazení. Co s tím teď? :/
         */

    }
}