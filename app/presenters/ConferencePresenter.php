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


    public function renderTalks()
    {
        $this->template->talks = $this->talkModel->find();
        $this->template->votes = $this->votingModel->votesAll();
        if ($this->getUser()->isLoggedIn()) {
            $this->template->p_voted = $this->votingModel->participantVoted($this->getUser()->getId());
        }
    }


    public function renderTalksDetail(string $guid): void
    {


    }


    /**
     * @return UI\Form
     */
    protected function createComponentVotingForm(): UI\Form
    {
        $form = new UI\Form;
        $talks = $this->talkModel->find();
        foreach ($talks as $talk) {
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
        $this->votingModel->clearVotes($participantId);
        foreach ($values as $key => $value) {
            if ($value == true) {
                if (!$this->votingModel->checkIfExist($participantId, $key)) {
                    $this->votingModel->insert($participantId, $key);
                }
            }
        }
        $this->flashMessage("Hlasování bylo úspěšné!");
    }
}