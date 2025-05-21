<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Page>
 */
class NewPage extends BaseForm
{
    #[Form\Field]
    public string $title = '';
}
