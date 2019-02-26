<?php

namespace App\Presenters;

use App\Model\User;
use DateTime;
use Nette\Application\UI;
use Nette\Security\Passwords;

final class RegistracePresenter extends BasePresenter
{
    /**
     * @var User
     */
    private $userModel;


    public function __construct(User $userModel)
{
    parent::__construct();
    $this->userModel = $userModel;
}


    //TODO Formuláře jinam než do presenteru
    protected function createComponentRegistrationForm(): UI\Form
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
        $form->addText('job', 'Co děláš:');
        $form->addText('job_desc', 'Kde to děláš');

        $form->addTextArea('bio', 'Něco o tobě:');

        $form->addCheckbox('newsletter_barcamp', 'Přeješ si dostávat novinky z Barcampu?');
        $form->addCheckbox('newsletter_techheaven', 'Přeješ si dostávat novinky z TechHeaven?');
        $form->addSubmit('register', 'Registrovat');

        $form->addProtection('Z důvodu bezpečnosti prosím odešlete tento formulář ještě jednou');

        $form->onSuccess[] = [$this, 'registrationFormSucceeded'];
        return $form;
    }


    public function registrationFormSucceeded(UI\Form $form): void
    {

        $values = $form->values;

        if ($this->userModel->isEmailExists($values['email'])) {
            $form->addError('S tímto e-mailem už jsi zaregistrovaný');
            return;
        }

        $toDb = [
            'full_name' => $values->first_name . " " . $values->last_name,
            'email' => $values->email,
            'bio' => $values->bio,
            'newsletter_barcamp' => $values->newsletter_barcamp,
            'newsletter_techheaven' => $values->newsletter_techheaven,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => new DateTime(),
            'position' => $values->position,
            'password' => Passwords::hash($values->pass),
            'job' => $values->job . "|||" . $values->job_desc,

        ];
        $this->userModel->insert($toDb);
        $this->redirect('Registrace:success');
    }

    //TODO Prezentace formulář

    protected function createComponentNewTalkForm()
    {
        $form = new UI\Form;
        $form->addText('title', 'Název přednášky')
            ->setRequired("Vyplňte prosím název přednášky");
        $form->addCheckbox('for_developer', 'Vývojářům')
            ->setDefaultValue(false);
        $form->addCheckbox('for_web', 'Webařům')
            ->setDefaultValue(false);
        $form->addCheckbox('for_marketing', 'Markeťákům')
            ->setDefaultValue(false);
        $form->addCheckbox('for_business', 'Byznysákům')
            ->setDefaultValue(false);
        $form->addCheckbox('for_other', 'Jiné')
            ->setDefaultValue(false);




        $form->addTextArea('anotation', 'Anotace tvé přednášky')
            ->setRequired("Vyplňte prosím anotaci přednášky");


        $form->addSubmit('submit', 'Registrovat');
        $form->onSuccess[] = [$this, 'newTalkFormSucceeded'];
        return $form;
    }

    public function newTalkFormSucceeded(UI\Form $form, \stdClass $values)
    {
        //TODO
    }
}
