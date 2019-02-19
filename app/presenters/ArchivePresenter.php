<?php

namespace App\Presenters;

use App\Model\ArchiveLoader;
use Nette\Application\Responses\TextResponse;
use Nette\Http\IResponse;


/**
 * Homepage presenter.
 */
class ArchivePresenter extends BasePresenter
{

    /**
     * @var ArchiveLoader
     */
    private $archiveLoader;


    /**
     * @param ArchiveLoader $archiveLoader
     */
    public function __construct(ArchiveLoader $archiveLoader)
    {
        $this->archiveLoader = $archiveLoader;
        parent::__construct();
    }


    /**
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    public function render2014(?string $path): void
    {
        $this->render(2014, $path);
    }


    /**
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    public function render2015(?string $path): void
    {
        $this->render(2015, $path);
    }


    /**
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    public function render2016(?string $path): void
    {
        $this->render(2016, $path);
    }


    /**
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    public function render2017(?string $path): void
    {
        $this->render(2017, $path);
    }


    /**
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    public function render2018(?string $path): void
    {
        $this->render(2018, $path);
    }


    /**
     * @param int $year
     * @param string|null $path
     * @throws \Nette\Application\BadRequestException
     */
    protected function render(int $year, ?string $path): void
    {
        $path = rtrim("$year/$path", '/');
        $archive = $this->archiveLoader->load($path);

        if($archive['status'] !== IResponse::S200_OK) {
            $this->error();
        }

        $this->sendResponse(new TextResponse($archive['content']));
    }

}
