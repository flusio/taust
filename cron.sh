#!/bin/sh
CURRENT_DIR=`dirname "$0"`
php $CURRENT_DIR/cli --request /domains/heartbeats > /dev/null
php $CURRENT_DIR/cli --request /alarms/monitor > /dev/null
php $CURRENT_DIR/cli --request /alarms/notify > /dev/null

[ $(( $RANDOM % 60 )) == 0 ] && php $CURRENT_DIR/cli --request /system/clear-old > /dev/null
