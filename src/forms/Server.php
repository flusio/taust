<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Server>
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Server extends BaseForm
{
    #[Form\Field(bind: 'setHostname')]
    public string $hostname = '';
}
