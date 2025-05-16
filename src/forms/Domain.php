<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Domain>
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Domain extends BaseForm
{
    #[Form\Field(bind: 'setId')]
    public string $id = '';
}
