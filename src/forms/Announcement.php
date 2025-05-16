<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Announcement>
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Announcement extends BaseForm
{
    #[Form\Field]
    public string $type;

    #[Form\Field]
    public \DateTimeImmutable $planned_at;

    #[Form\Field(transform: 'trim')]
    public string $title;

    #[Form\Field(transform: 'trim')]
    public string $content;
}
