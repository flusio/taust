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
    public \DateTimeImmutable $planned_at;

    #[Form\Field(transform: 'trim')]
    public string $title;

    #[Form\Field(transform: 'trim')]
    public string $content;
}
