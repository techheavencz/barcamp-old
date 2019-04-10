<?php

namespace App\Presenters;

use App\Model\NotFoundException;
use App\Model\User;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

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


    /**
     * @param int $id
     * @param string $hash
     * @param string $isAttending Values: 0/1
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDefault($id, $hash, $isAttending): void
    {
        try {
            $user = $this->userModel->getById($id);
        } catch (NotFoundException $e) {
            throw new BadRequestException();
        }
        if (strtolower($hash) !== strtolower(md5($user->email))) {
            throw new ForbiddenRequestException('Nesouhlasí kontrolní hash');
        }

        $this->userModel->setAttending($id, (bool)$isAttending);
        $this->flashMessage('Děkujeme za informaci!', 'success');
        $this->redirect('Homepage:default');
    }

}
