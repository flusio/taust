#!/bin/sh
php ./cli --request /domains/heartbeats > /dev/null
php ./cli --request /alarms/monitor > /dev/null
php ./cli --request /alarms/notify > /dev/null
