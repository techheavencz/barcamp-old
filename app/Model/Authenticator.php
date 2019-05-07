<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

class Authenticator implements IAuthenticator
{

    /**
     * @var User
     */
    private $userModel;


    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }


    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @param array $credentials
     * @return Identity
     * @throws AuthenticationException
     */

    public function authenticate(array $credentials): IIdentity
    {
        $email = $credentials[self::USERNAME];

        try {
            $user = $this->userModel->getByEmail($email);
        } catch (NotFoundException $e) {
            throw new AuthenticationException('User not found', self::IDENTITY_NOT_FOUND, $e);
        }

        $id = $user['id'];

        $isValid = $this->userModel->verifyPassword($user, $credentials[self::PASSWORD]);

        if ($isValid !== true) {
            throw new AuthenticationException('Password mismatch', self::INVALID_CREDENTIAL);
        }

        return new Identity($id, [], $user->toArray());
    }
}
