<?php

namespace App\Presenters;


use App\Model\Talk;
use App\Model\Voting;
use Nette\Application\UI;

final class ConferencePresenter extends BasePresenter
{
    /**
     * @var Talk
     */
    private $talkModel;

    /**
     * @var Voting
     */
    private $votingModel;

    public function __construct(Talk $talkModel, Voting $votingModel)
    {
        parent::__construct();
        $this->talkModel = $talkModel;
        $this->votingModel = $votingModel;
    }


    public function renderProfil()
    {
        //TODO Zobrazení detailu profilu

    }


    public function renderTalks()
    {
        $this->template->talks = $this->talkModel->find();
        $this->template->votes = $this->votingModel->votesAll();
    }

    public function renderTalksVoting()
    {
        $this->template->talks = $this->talkModel->find();
    }

    /**
     * @return UI\Form
     */
    protected function createComponentVotingForm(): UI\Form
    {
        $form = new UI\Form;
        $talks = $this->talkModel->find();
        foreach($talks as $talk) {
            $form->addCheckbox($talk->id, "Talk");
        }
        $form->onSuccess[] = [$this, 'votingFormSucceeded'];
        return $form;
    }


    /**
     * @param UI\Form $form
     */
    public function votingFormSucceeded(UI\Form $form): void
    {
        $participantId = $this->user->getIdentity()->getId();
        $values = $form->values;

        foreach($values as $key => $value) {
            if($value == true) {
                if(!$this->votingModel->checkIfExist($participantId, $key)) {
                $this->votingModel->insert($participantId, $key);
                }
            }
        }
        $this->flashMessage("Hlasování bylo úspěšné!");
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