<?php

namespace taust\cli;

use Minz\Response;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Help
{
    public function show()
    {
        $usage = "Usage: php cli COMMAND [--OPTION=VALUE]...\n";
        $usage .= "\n";
        $usage .= "COMMAND can be one of the following:\n";
        $usage .= "  help                 Show this help\n";
        $usage .= "\n";
        $usage .= "  alarms monitor       Check domains and servers to find any new or outdated alarms\n";
        $usage .= "  alarms notify        Send alarms notifications if any\n";
        $usage .= "\n";
        $usage .= "  domains heartbeats   Check that monitored domains are up (port 443)\n";
        $usage .= "\n";
        $usage .= "  users create         Create a user\n";
        $usage .= "      --username=USERNAME\n";
        $usage .= "      --password=PASSWORD\n";
        $usage .= "\n";
        $usage .= "  system clear-old     Clear old data\n";
        $usage .= "  system setup         Initialize or update the system\n";

        return Response::text(200, $usage);
    }
}
