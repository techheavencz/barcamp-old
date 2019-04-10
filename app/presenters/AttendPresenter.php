<?php

namespace App\Presenters;

use Nette;
use App\Model\User;

final class AttendPresenter extends BasePresenter
{
    /**
     * @var User
     */
    private $userModel;


    /**
     * @param User $userModel
     */
    public function __construct(User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
    }

    public function renderDefault($id, $hash, $isAttending) {
        if(is_null($id)) {
            throw new Nette\Application\BadRequestException();
        }
        $user = $this->userModel->getById($id);
        if($hash != md5($user->email)) {
            throw new Nette\Application\ForbiddenRequestException();
        }

        $this->userModel->setAttending($id, (bool) $isAttending);
        $this->flashMessage("Díky za info!");
        $this->redirect("Homepage:default");

    }

}
