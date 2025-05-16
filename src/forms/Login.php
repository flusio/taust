<?php

namespace taust\forms;

use Minz\Form;
use Minz\Validable;
use taust\models;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Login extends BaseForm
{
    #[Form\Field(transform: 'trim')]
    public string $username = '';

    #[Form\Field]
    public string $password = '';

    #[Validable\Check]
    public function checkCredentials(): void
    {
        try {
            $user = $this->user();
        } catch (\RuntimeException $e) {
            $this->addError('@base', 'invalid_credentials', _('Wrong credentials!'));
            return;
        }

        if (!$user->verifyPassword($this->password)) {
            $this->addError('@base', 'invalid_credentials', _('Wrong credentials!'));
        }
    }

    public function user(): models\User
    {
        $user = models\User::findBy(['username' => $this->username]);

        if (!$user) {
            throw new \RuntimeException("Unknown user {$this->username}");
        }

        return $user;
    }
}
