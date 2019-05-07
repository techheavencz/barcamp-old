<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Nette\Database;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
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
     * @var Passwords
     */
    private $passwords;


    /**
     * @param Database\Context $db
     * @param Passwords $passwords
     */
    public function __construct(Database\Context $db, Passwords $passwords)
    {
        $this->db = $db;
        $this->passwords = $passwords;
    }


    /**
     * @param int $id
     * @return ActiveRow
     * @throws NotFoundException
     */
    public function getById(int $id): ActiveRow
    {
        $user = $this->db->table(self::TABLE)->get($id);

        if ($user === null) {
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

        if ($user === null) {
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

        return $this->passwords->verify($password, $hash);
    }


    /**
     * @param ActiveRow $user
     * @return string
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function createResetPasswordToken(ActiveRow $user): string
    {
        $token = Random::generate(20);
        $tokenHash = $this->passwords->hash($token);
        $expire = new DateTime('+1 day');

        $user->update([
            self::RESET_TOKEN => $tokenHash,
            self::RESET_TOKEN_EXPIRE => $expire,
        ]);

        return $token;
    }


    /**
     * @param ActiveRow $user
     * @throws InvalidStateException
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
     * @throws Exception
     */
    public function verifyResetPasswordToken(ActiveRow $user, string $token): bool
    {
        $expire = $user[self::RESET_TOKEN_EXPIRE];
        $hash = $user[self::RESET_TOKEN];

        // Check token expiration
        $now = new DateTime();
        if ($expire === null || $expire < $now) {
            return false;
        }

        // Check token hash
        return $this->passwords->verify($token, $hash);
    }


    /**
     * @param ActiveRow $user
     * @param string $password
     * @throws InvalidStateException
     */
    public function updatePassword(ActiveRow $user, string $password): void
    {
        $hash = $this->passwords->hash($password);

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


    /**
     * @param $id
     * @param $attend
     * @return bool
     * @throws NotFoundException
     * @throws InvalidStateException
     */
    public function setAttending($id, $attend): bool
    {
        $user = $this->getById($id);

        if ($user->attending === null) {
            return $user->update(['attending' => $attend]);
        }
        return false;
    }
}
