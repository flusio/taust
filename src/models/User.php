<?php

namespace taust\models;

use Minz\Database;
use Minz\Translatable;
use Minz\Validable;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'users')]
class User
{
    use Database\Recordable;
    use Validable;

    #[Database\Column]
    public string $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Enter a username.'),
    )]
    #[Validable\Format(
        pattern: '/^[0-9a-zA-Z_\-]{1,}$/',
        message: new Translatable('Enter a valid username (only letters, numbers, underscores and/or hyphens).')
    )]
    public string $username;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Enter a password.'),
    )]
    public string $password_hash;

    #[Database\Column]
    #[Validable\Email(
        message: new Translatable('Enter a valid email.'),
    )]
    public ?string $email = null;

    #[Database\Column]
    public ?string $free_mobile_login = null;

    #[Database\Column]
    public ?string $free_mobile_key = null;

    public function __construct(string $username, string $password)
    {
        $this->id = \Minz\Random::timebased();
        $this->username = trim($username);
        $this->password_hash = $password ? password_hash($password, PASSWORD_BCRYPT) : '';
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function setEmail(?string $email): void
    {
        if ($email) {
            $email = \Minz\Email::sanitize($email);
        }

        $this->email = $email;
    }
}
