<?php

namespace taust\forms;

use Minz\Form;
use taust\models;

/**
 * @extends BaseForm<models\Announcement>
 */
class AnnouncementStatus extends BaseForm
{
    #[Form\Field]
    public string $status;
}
