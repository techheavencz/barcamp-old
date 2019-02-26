<?php

namespace App\Presenters;

use App\Model\Authenticator;
use App\Model\Mail;
use App\Model\NotFoundException;
use App\Model\User;
use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls\HiddenField;


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
    public function actionOut(): void
    {
        $this->user->logout(true);

        $this->flashMessage('Byl jsi odhlášen', 'success');
        $this->redirect('Homepage:default');
    }


    /**
     * @return UI\Form
     */
    protected function createComponentSignInForm(): UI\Form
    {
        $form = new UI\Form;
        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte prosím email');
        $form->addPassword('pass', 'Heslo:')
            ->setRequired('Zadejte prosím heslo');
        $form->addSubmit('sign', 'Přihlásit se');
        $form->addProtection('Z důvodu bezpečnosti prosím odešlete tento formulář ještě jednou');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }


    /**
     * @param UI\Form $form
     */
    public function signInFormSucceeded(UI\Form $form): void
    {
        $values = $form->values;

        $email = $values['email'];
        $password = $values['pass'];

        try {
            $this->user->login($email, $password);
        } catch (Nette\Security\AuthenticationException $e) {
            if ($e->getCode() === Authenticator::IDENTITY_NOT_FOUND) {
                $form->addError("Uživatele s e-mailem „${email}“ bohužel neznáme, jsi zaregistrován?");
            } else {
                $form->addError('Zadané přihlašovací údaje jsou neplatné');
            }
            return;
        }

        $this->redirect('Homepage:default');
    }


    public function renderReset(?string $email)
    {
        if($email !== null) {
            /** @var UI\Form $form */
            $form = $this['resetPasswordForm'];

            /** @var HiddenField $emailInput */
            $emailInput = $form['email'];

            $emailInput->setDefaultValue($email);
        }
    }

    /**
     * @param string $email
     * @param string $token
     * @throws NotFoundException
     * @throws \Exception
     */
    public function renderResetVerify(string $email, string $token): void
    {
        $user = $this->userModel->getByEmail($email);

        $isValid = $this->userModel->verifyResetPasswordToken($user, $token);

        if ($isValid !== true) {
            $this->flashMessage('Omlouváme se, ale odkaz je neplatný, pošlete si nový', 'error');
            $this->redirect('reset', ['email' => $email]);
        }

        /** @var UI\Form $form */
        $form = $this['newPasswordForm'];

        /** @var HiddenField $emailInput */
        $emailInput = $form['email'];
        /** @var HiddenField $tokenInput */
        $tokenInput = $form['token'];

        $emailInput->setDefaultValue($email);
        $tokenInput->setDefaultValue($token);
    }


    /**
     * @return UI\Form
     */
    public function createComponentResetPasswordForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadej prosím Váš e-mail, kterým jsi se registroval(a).');
        $form->addSubmit('submit', 'Resetovat');

        $form->addProtection('Z důvodu bezpečnosti prosím odešlete tento formulář ještě jednou');

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
            $form->addError("Uživatele s e-mailem „${email}“ bohužel neznáme, jsi zaregistrován?");
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
            ->setRequired('Zadejte prosím heslo znovu')
            ->addRule(UI\Form::EQUAL, 'Hesla se neshodují', $form['pass']);
        $form->addSubmit('submit', 'Změnit heslo');

        $form->addHidden('email');
        $form->addHidden('token');

        $form->addProtection('Z důvodu bezpečnosti prosím odešlete tento formulář ještě jednou');

        $form->onSuccess[] = [$this, 'newPasswordFormSuccess'];

        return $form;
    }


    /**
     * @param UI\Form $form
     * @throws Nette\InvalidStateException
     * @throws NotFoundException
     */
    public function newPasswordFormSuccess(UI\Form $form): void
    {
        $email = $form->values['email'];
        $token = $form->values['token'];

        $user = $this->userModel->getByEmail($email);

        $isValid = $this->userModel->verifyResetPasswordToken($user, $token);

        if ($isValid !== true) {
            $this->flashMessage('Omlouváme se, ale odkaz je neplatný, pošlete si nový', 'error');
            $this->redirect('reset', ['email' => $email]);
        }

        $password = $form->values['pass'];

        $this->userModel->updatePassword($user, $password);

        // Reset token can be used once only
        $this->userModel->removeResetPasswordToken($user);

        $this->flashMessage('Vaše heslo bylo nastaveno', 'success');
        $this->redirect('Homepage:default');
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