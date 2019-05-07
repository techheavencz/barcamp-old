<?php

namespace App\Presenters;


use App\Model\NotFoundException;
use App\Model\Talk;
use App\Model\Voting;
use Nette\Application\BadRequestException;
use Nette\Application\UI;
use Nette\InvalidArgumentException;
use Nette\MemberAccessException;
use Nette\Security\IIdentity;

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


    public function renderTalks(): void
    {
        $this->template->talks = $this->talkModel->find();
        $this->template->votes = $this->votingModel->votesAll();
        if ($this->user->isLoggedIn()) {
            $this->template->p_voted = $this->votingModel->participantVoted($this->user->getId());
        }
    }


    /**
     *  Redirect logged user to talks, unlogged to login form
     */
    public function actionVote(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect('talks');
        } else {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }


    /**
     * @param string $guid
     * @throws BadRequestException
     * @throws MemberAccessException
     */
    public function renderTalksDetail(string $guid): void
    {
        try {
            $this->template->talk = $this->talkModel->getByGuid($guid);
        } catch (NotFoundException $e) {
            throw new BadRequestException();
        }
    }


    /**
     * @return UI\Form
     */
    protected function createComponentVotingForm(): UI\Form
    {
        $form = new UI\Form;
        $talks = $this->talkModel->find();
        foreach ($talks as $talk) {
            $form->addCheckbox($talk->id, 'Talk');
        }
        $form->onSuccess[] = [$this, 'votingFormSucceeded'];
        return $form;
    }


    /**
     * @param UI\Form $form
     * @throws InvalidArgumentException
     * @throws BadRequestException
     */
    public function votingFormSucceeded(UI\Form $form): void
    {
        if ($this->user->isLoggedIn() === false) {
            throw new BadRequestException('User not logged');
        }

        /** @var IIdentity $identity */
        $identity = $this->user->getIdentity();
        $participantId = $identity->getId();
        $values = $form->values;
        $this->votingModel->clearVotes($participantId);
        foreach ($values as $key => $value) {
            if ($value && $this->votingModel->checkIfExist($participantId, $key) === false) {
                $this->votingModel->insert($participantId, $key);
            }
        }
        $this->flashMessage('Hlasování bylo úspěšné!');
    }
}