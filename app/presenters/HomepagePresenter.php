<?php

namespace App\Presenters;

use App\Model\Contact;


final class HomepagePresenter extends BasePresenter
{
    /**
     * @var Contact
     */
    private $contactModel;


    public function __construct(Contact $contactModel)
    {
        parent::__construct();
        $this->contactModel = $contactModel;
    }


    public function renderContact(): void
    {
        $this->template->organizators = $this->contactModel->findOrganisators();
        $this->template->asistants = $this->contactModel->findAsistents();
    }


    public function renderDefault()
    {

    }


    public function renderInfo()
    {

    }


    public function renderPartners()
    {

    }


    public function renderPrivacyPolicy()
    {

    }


    public function renderTerms()
    {

    }


    public function renderVocabulary()
    {

    }


    public function renderWritten()
    {

    }
}
