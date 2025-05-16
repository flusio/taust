<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Domain>
 */
class Domain extends BaseForm
{
    #[Form\Field(bind: 'setId')]
    public string $id = '';
}
