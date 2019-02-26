<?php

namespace App\Presenters;

use App\Model\Mail;
use App\Model\NotFoundException;
use App\Model\User;
use Nette;
use Nette\Application\UI;


final class SignPresenter extends BasePresenter
{
    /**
     * @var User
     */
    private $userModel;
    /**
     * @var Mail
     */
    private $mailModel;


    /**
     * @param User $user
     * @param Mail $mailModel
     */
    public function __construct(User $user, Mail $mailModel)
    {
        parent::__construct();
        $this->userModel = $user;
        $this->mailModel = $mailModel;
    }


    /**
     *
     */
    public function renderIn()
    {
        //TODO Sign in
    }


    /**
     * @return UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = new UI\Form;
        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte prosím email');
        $form->addPassword('pass', 'Heslo:')
            ->setRequired('Zadejte prosím heslo');
        $form->addSubmit('sign', 'Přihlásit se');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }


    /**
     * @param UI\Form $form
     * @param \stdClass $values
     */
    public function signInFormSucceeded(UI\Form $form, \stdClass $values)
    {
        //TODO sign in
        $this->redirect('Homepage:default');
    }


    /**
     * @param $email
     * @param $token
     * @throws NotFoundException
     * @throws \Exception
     */
    public function renderResetVerify($email, $token): void
    {
        $user = $this->userModel->getByEmail($email);

        $isValid = $this->userModel->verifyResetPasswordToken($user, $token);

        if ($isValid !== true) {
            $this->flashMessage('Omlouváme se, ale odkaz je neplatný, pošlete si nový', 'error');
            $this->redirect('reset', ['email' => $email]);
        }


    }


    /**
     * @return UI\Form
     */
    public function createComponentResetPasswordForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addEmail('email', 'E-mail:')
            ->setRequired('zadejte prosím Váš e-mail, kterým jsi se registroval(a).');
        $form->addSubmit('submit', 'Resetovat');

        $form->onSuccess[] = [$this, 'resetFormSuccess'];

        return $form;
    }


    /**
     * @param UI\Form $form
     * @throws Nette\InvalidArgumentException
     * @throws Nette\InvalidStateException
     * @throws Nette\Mail\SendException
     * @throws UI\InvalidLinkException
     */
    public function resetFormSuccess(UI\Form $form): void
    {
        $email = $form->values['email'];

        try {
            $user = $this->userModel->getByEmail($email);

            $message = $this->createResetPasswordMail($user);
            $message->addTo($email);

            $this->mailModel->send($message);

            $this->flashMessage('Byl vám odeslán e-mail s dalšími instrukcemi', 'success');
            $this->redirect('Sign:in');
        } catch (NotFoundException $e) {
            $form->addError("Uživatele s e-mailem $email bohužel neznáme, jsi zaregistrován?");
            return;
        }
    }


    /**
     * @return UI\Form
     */
    public function createComponentNewPasswordForm(): UI\Form
    {
        $form = new UI\Form;


        $form->addPassword('pass', 'Heslo:')
            ->setRequired('Zadejte prosím heslo');
        $form->addPassword('pass_repeat')
            ->setRequired('Zadejte prosím heslo znovu');
        $form->addSubmit('submit', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'newPasswordFormSuccess'];

        return $form;
    }


    /**
     * @param UI\Form $form
     */
    public function newPasswordFormSuccess(UI\Form $form): void
    {
        // TODO: Don't forget enable this invalidation
        // Reset token can be used once only
        // $this->removeResetPasswordToken($user);
    }




    /**
     * @param Nette\Database\Table\ActiveRow $user
     * @return Nette\Mail\Message
     * @throws Nette\InvalidArgumentException
     * @throws Nette\InvalidStateException
     * @throws UI\InvalidLinkException
     */
    protected function createResetPasswordMail(Nette\Database\Table\ActiveRow $user): Nette\Mail\Message
    {
        $token = $this->userModel->createResetPasswordToken($user);
        $url = $this->link('//resetVerify', ['email' => $user['email'], 'token' => $token]);


        $message = new Nette\Mail\Message();

        $message->setBody("Klikněte prosím zde: $url")
            ->setSubject('Reset hesla');

        return $message;
    }

}