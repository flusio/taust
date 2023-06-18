<?php

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */

\taust\jobs\Cleaner::install();
\taust\jobs\CheckHeartbeats::install();
\taust\jobs\CheckAlarms::install();
\taust\jobs\NotifyAlarms::install();
