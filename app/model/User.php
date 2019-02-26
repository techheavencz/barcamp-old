<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

class User
{
    protected const TABLE = 'participants';
    protected const RESET_TOKEN = 'reset_token';
    protected const RESET_TOKEN_EXPIRE = 'reset_token_expire';
    protected const EMAIL = 'email';
    protected const PASSWORD = 'password';

    /**
     * @var Database\Context
     */
    private $db;


    /**
     * @param Database\Context $db
     */
    public function __construct(Database\Context $db)
    {
        $this->db = $db;
    }


    /**
     * @param int $id
     * @return ActiveRow
     * @throws NotFoundException
     */
    public function getById(int $id): ActiveRow
    {
        $user = $this->db->table(self::TABLE)->get($id);

        if ($user === false) {
            throw new NotFoundException("Not found user with ID: $id");
        }

        /** @var ActiveRow $user */
        return $user;
    }

    /**
     * @param string $email
     * @return ActiveRow
     * @throws NotFoundException
     */
    public function getByEmail(string $email): ActiveRow
    {
        $user = $this->db->table(self::TABLE)->where(self::EMAIL, $email)->fetch();

        if ($user === false) {
            throw new NotFoundException("Not found user with e-mail: $email");
        }

        /** @var ActiveRow $user */
        return $user;
    }


    /**
     * @param string $email
     * @return bool
     */
    public function isEmailExists(string $email): bool
    {
        try {
            $this->getByEmail($email);
        } catch (NotFoundException $e) {
            return false;
        }
        return true;
    }


    /**
     * @param ActiveRow $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(ActiveRow $user, string $password): bool
    {
        $hash = $user[self::PASSWORD];

        return Passwords::verify($password, $hash);
    }

    /**
     * @param ActiveRow $user
     * @return string
     * @throws \Nette\InvalidArgumentException
     * @throws \Nette\InvalidStateException
     * @throws \Exception
     */
    public function createResetPasswordToken(ActiveRow $user): string
    {
        $token = Random::generate(20);
        $tokenHash = Passwords::hash($token);
        $expire = new DateTime('+1 day');

        $user->update([
            self::RESET_TOKEN => $tokenHash,
            self::RESET_TOKEN_EXPIRE => $expire,
        ]);

        return $token;
    }


    /**
     * @param ActiveRow $user
     * @throws \Nette\InvalidStateException
     */
    public function removeResetPasswordToken(ActiveRow $user): void
    {
        $user->update([
            self::RESET_TOKEN => null,
            self::RESET_TOKEN_EXPIRE => null,
        ]);
    }


    /**
     * @param ActiveRow $user
     * @param string $token
     * @return bool
     * @throws \Exception
     */
    public function verifyResetPasswordToken(ActiveRow $user, string $token): bool
    {
        $expire = $user[self::RESET_TOKEN_EXPIRE];
        $hash = $user[self::RESET_TOKEN];
        
        // Check token expiration
        $now = new DateTime();
        if($expire === null || $expire < $now) {
                return false;
        }

        // Check token hash
        return Passwords::verify($token, $hash);
    }


    /**
     * @param ActiveRow $user
     * @param string $password
     * @throws \Nette\InvalidStateException
     */
    public function updatePassword(ActiveRow $user, string $password): void
    {
        $hash = Passwords::hash($password);

        $user->update([
            self::PASSWORD => $hash,
        ]);
    }


    /**
     * @param array $data
     * @return ActiveRow
     */
    public function insert(array $data): ActiveRow
    {
        return $this->db->table(self::TABLE)->insert($data);
    }
}
