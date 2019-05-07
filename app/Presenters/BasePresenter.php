<?php

namespace App\Presenters;

use Nette;


class BasePresenter extends Nette\Application\UI\Presenter
{
    protected function beforeRender(): void
    {
        parent::beforeRender();
        $parameters = $this->context->getParameters();
        $this->template->wwwDir = $parameters['wwwDir'];
    }

}
