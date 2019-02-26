<?php
declare(strict_types=1);

namespace App\Model;

class Identity extends \Nette\Security\Identity
{
    public function isRegistered(): bool
    {
        //TODO
        return false;
    }
}
