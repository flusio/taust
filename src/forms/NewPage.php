<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Page>
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class NewPage extends BaseForm
{
    #[Form\Field]
    public string $title = '';
}
