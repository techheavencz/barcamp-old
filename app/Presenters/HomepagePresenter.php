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

        $this->template->addFilter('photosUrlize', function ($path) {
            if (preg_match('~^https?://~', $path)) {
                return $path;
            }

            $basePath = $this->getHttpRequest()->getUrl()->getBasePath();
            return $basePath . 'media/photos/' . $path;
        });
    }
}
