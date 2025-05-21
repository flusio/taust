<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Server>
 */
class Server extends BaseForm
{
    #[Form\Field(bind: 'setHostname')]
    public string $hostname = '';
}
