<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Announcement>
 */
class Announcement extends BaseForm
{
    #[Form\Field]
    public string $type;

    #[Form\Field]
    public string $planned_at;

    #[Form\Field]
    public string $title;

    #[Form\Field]
    public string $content;
}
