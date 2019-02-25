<?php

namespace App\Presenters;

use Nette;


final class SignPresenter extends BasePresenter
{
    public function renderIn()
    {
        //TODO Sign in
    }
    protected function createComponentSignInForm()
    {
        $form = new UI\Form;
        $form->addText('email', 'E-mail:')
            ->setRequired('Zadejte prosím email');
        $form->addPassword('pass', 'Heslo:')
            ->setRequired('Zadejte prosím heslo');
        $form->addSubmit('sigin', 'Přihlásit se');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(UI\Form $form, \stdClass $values)
    {
        //TODO sign in
        $this->redirect('Homepage:default');
    }
}