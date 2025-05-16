<?php

namespace taust\forms;

use Minz\Form;
use Minz\Validable;
use taust\models;

/**
 * @extends BaseForm<models\User>
 */
class User extends BaseForm
{
    #[Form\Field(transform: 'trim', bind: 'setEmail')]
    public string $email = '';

    #[Form\Field(transform: 'trim')]
    public string $free_mobile_login = '';

    #[Form\Field(transform: 'trim')]
    public string $free_mobile_key = '';
}
