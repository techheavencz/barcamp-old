<?php

namespace App\Presenters;

use App\Model\Authenticator;
use App\Model\Mail;
use App\Model\NotFoundException;
use App\Model\User;
use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls\HiddenField;
use Nette\Security\Passwords;
use Tracy\Debugger;


final class SignPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    /**
     * @var User
     */
    private $userModel;

    /**
     * @var Mail
     */
    private $mailModel;
    /**
     * @var Passwords
     */
    private $passwords;


    /**
     * @param User $user
     * @param Mail $mailModel
     * @param Passwords $passwords
     */
    public function __construct(User $user, Mail $mailModel, Passwords $passwords)
    {
        parent::__construct();
        $this->userModel = $user;
        $this->mailModel = $mailModel;
        $this->passwords = $passwords;
    }


    /**
     * @throws Nette\InvalidStateException
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();

        // Start sign-in form to generate Csrf token before send HTTP headers
        $this->session->start();
    }


    /**
     *
     */
    public function actionOut(): void
    {
        $userId = $this->user->id;
        Debugger::log("[Sign:out] Success, User ID: $userId", 'sign');

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


    public function renderIn(): void
    {
        if($this->user->isLoggedIn()) {
            $this->flashMessage('Vy už jste přihlášeni', 'success');
            $this->redirect('Homepage:default');
        }
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

            $userId = $this->user->id;
            Debugger::log("[Sign:in] Success, User ID: $userId", 'sign');

        } catch (Nette\Security\AuthenticationException $e) {
            if ($e->getCode() === Authenticator::IDENTITY_NOT_FOUND) {
                $form->addError("Uživatele s e-mailem „${email}“ bohužel neznáme, jsi zaregistrován?");
                Debugger::log("[Sign:in] Fail, Unknown email: $email", 'sign');
            } else {
                $form->addError('Zadané přihlašovací údaje jsou neplatné');
                Debugger::log("[Sign:in] Fail, Wrong password for email: $email", 'sign');
            }
            return;
        }

        $this->restoreRequest($this->backlink);
        $this->redirect('Homepage:default');
    }


    /**
     * @param string|null $email
     */
    public function renderReset(?string $email): void
    {
        if ($email !== null) {
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
            Debugger::log("[Sign:reset] Fail, Open form for email: $email", 'sign');

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

        Debugger::log("[Sign:reset] Success, Open form for email: $email", 'sign');
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

            Debugger::log("[Sign:reset] Success, Send token for email: $email", 'sign');

            $this->flashMessage('Byl vám odeslán e-mail s dalšími instrukcemi', 'success');
            $this->redirect('Sign:in');
        } catch (NotFoundException $e) {
            Debugger::log("[Sign:reset] Fail, Send token for email: $email", 'sign');
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
     * @throws \Exception
     */
    public function newPasswordFormSuccess(UI\Form $form): void
    {
        $email = $form->values['email'];
        $token = $form->values['token'];

        $user = $this->userModel->getByEmail($email);

        $isValid = $this->userModel->verifyResetPasswordToken($user, $token);

        if ($isValid !== true) {
            Debugger::log("[Sign:reset] Fail, Submit reset for email: $email", 'sign');

            $this->flashMessage('Omlouváme se, ale odkaz je neplatný, pošlete si nový', 'error');
            $this->redirect('reset', ['email' => $email]);
        }

        $password = $form->values['pass'];

        $this->userModel->updatePassword($user, $password);

        // Reset token can be used once only
        $this->userModel->removeResetPasswordToken($user);

        Debugger::log("[Sign:reset] Success, Submit reset for email: $email", 'sign');

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