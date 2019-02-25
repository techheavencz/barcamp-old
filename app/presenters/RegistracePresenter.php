<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;
use Nette\Security\Passwords;

final class RegistracePresenter extends BasePresenter
{

    //TODO Formuláře jinam než do presenteru
    protected function createComponentRegistrationForm()
    {
        $form = new UI\Form;
        $form->addText('first_name', 'Jméno:')
            ->setRequired('Zadejte prosím jméno');
        $form->addText('last_name', 'Příjmení:')
            ->setRequired('Zadejte prosím příjmení');
        $form->addPassword('pass', 'Heslo:')
            ->setRequired('Zadejte prosím heslo');
        $form->addPassword('pass_repeat', 'Heslo znovu:')
            ->setRequired('Zadejte prosím heslo znovu')
            ->addRule(UI\Form::EQUAL, 'Hesla se neshodují', $form['pass']);
        $form->addText('email', 'E-mail:')
        ->setRequired('Zadejte prosím email');
        $form->addText('position', 'Pozice:');
        $form->addText('job', "Co děláš:");
        $form->addText('job_desc', "Kde to děláš");

        $form->addTextArea('bio', 'Něco o tobě:');

        $form->addCheckbox('newsletter_barcamp', 'Přeješ si dostávat novinky z Barcampu?');
        $form->addCheckbox('newsletter_techheaven', 'Přeješ si dostávat novinky z TechHeaven?');
        $form->addSubmit('register', 'Registrovat');
        $form->onSuccess[] = [$this, 'registrationFormSucceeded'];
        return $form;
    }

    public function registrationFormSucceeded(UI\Form $form, \stdClass $values)
    {

        $row = $this->db->table('participants')->where("email", $values->email)->count();
        if($row > 0) {
            $form->addError("S tímto e-mailem už zaregistrovaný jste.");
            return;
        }
        $toDb = [
            'full_name' => $values->first_name . " " . $values->last_name,
            'email' => $values->email,
            'bio' => $values->bio,
            'newsletter_barcamp' => $values->newsletter_barcamp,
            'newsletter_techheaven' => $values->newsletter_techheaven,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => date ("Y-m-d H:i:s", time()),
            'position' => $values->position,
            'password' => Passwords::hash($values->pass),
            'job' => $values->job . "|||" . $values->job_desc,

            ];
        $this->db->table('participants')->insert($toDb);
        $this->redirect('Registrace:success');
    }

    //TODO "Password reset" formulář


    //TODO Prezentace formulář

    /*
     * Pokud je uživatel přihlášen, předvyplnit údaje? Nebo se na ně vůbec neptat a umožnit jen přihlašeným?
     * Fotka
     */
}