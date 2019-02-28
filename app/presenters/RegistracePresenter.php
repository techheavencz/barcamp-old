<?php

namespace App\Presenters;

use App\Model\Talk;
use App\Model\User;
use DateTime;
use Nette\Application\UI;
use Nette\Security\Passwords;
use Nette\Utils\Json;

final class RegistracePresenter extends BasePresenter
{
    /**
     * @var User
     */
    private $userModel;
    /**
     * @var Talk
     */
    private $talkModel;


    /**
     * @param User $userModel
     * @param Talk $talkModel
     */
    public function __construct(User $userModel, Talk $talkModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->talkModel = $talkModel;
    }


    /**
     * @throws \Nette\InvalidStateException
     */
    protected function renderDefault(): void
    {
        // Start sign-in form to generate Csrf token before send HTTP headers
        $this->session->start();
    }

    //TODO Formuláře jinam než do presenteru


    /**
     * @return UI\Form
     */
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


    /**
     * @param UI\Form $form
     * @throws \Nette\InvalidStateException
     */
    public function registrationFormSucceeded(UI\Form $form): void
    {

        $values = $form->values;

        if ($this->userModel->isEmailExists($values['email'])) {
            $form->addError('S tímto e-mailem už jsi zaregistrovaný');
            return;
        }

        $toDb = [
            'full_name' => $values->first_name . ' ' . $values->last_name,
            'email' => $values->email,
            'bio' => $values->bio,
            'newsletter_barcamp' => $values->newsletter_barcamp,
            'newsletter_techheaven' => $values->newsletter_techheaven,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => new DateTime(),
            'position' => $values->position,
            'password' => Passwords::hash($values->pass),
            'job' => $values->job . '|||' . $values->job_desc,

        ];
        $this->userModel->insert($toDb);
        $this->redirect('Registrace:success');
    }


    /**
     *
     */
    public function renderTalk(): void
    {
        if($this->user->isLoggedIn() !== true) {
            $this->flashMessage('Pro registraci přednášky se prosím nejdříve přihlaste.');
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }
    
    /**
     * @return UI\Form
     */
    protected function createComponentNewTalkForm(): UI\Form
    {
        $form = new UI\Form;
        $form->addText('title', 'Název přednášky')
            ->setRequired('Vyplňte prosím název přednášky');

        $form->addCheckboxList('target', 'Komu je přednáška určena?', [
            'developer' => 'Vývojářům',
            'web' => 'Webařům',
            'marketing' => 'Markeťákům',
            'business' => 'Byznysákům',
            'other' => 'Jiné',
        ]);
        $form->addTextArea('annotation', 'Anotace tvé přednášky')
            ->setRequired('Vyplňte prosím anotaci přednášky');

        $form->addSubmit('submit', 'Registrovat');

        $form->addProtection('Z důvodu bezpečnosti prosím odešlete tento formulář ještě jednou');

        $form->onSuccess[] = [$this, 'newTalkFormSucceeded'];
        return $form;
    }


    /**
     * @param UI\Form $form
     * @throws \Nette\Utils\JsonException
     */
    public function newTalkFormSucceeded(UI\Form $form): void
    {
        $values = $form->values;

        $title = $values['title'];

        $data = [
            'title' => $title,
            'annotation' => $values['annotation'],
            'target' => Json::encode($values['target']),

            'created' => new DateTime,
        ];

        $userId = $this->user->id;

        $this->talkModel->insert($data, $userId);

        $this->flashMessage("Tvoje přednáška „${title}“ byla zveřejněna.", 'success');
        $this->redirect('Conference:talks');
    }
}
