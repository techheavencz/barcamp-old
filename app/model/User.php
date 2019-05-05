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

        return $this->passwords->verify($password, $hash);
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
        return $this->passwords->verify($token, $hash);
    }


    /**
     * @param ActiveRow $user
     * @param string $password
     * @throws \Nette\InvalidStateException
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
     * @throws \Nette\InvalidStateException
     */
    public function setAttending($id, $attend): bool
    {
        $row = $this->db->table(self::TABLE)->get($id);
        if($row->attending === null) {
            return $row->update(['attending' => $attend]);
        }
        return false;
    }
}
