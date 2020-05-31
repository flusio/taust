#!/bin/sh
CURRENT_DIR=`dirname "$0"`
php $CURRENT_DIR/cli --request /domains/heartbeats > /dev/null
php $CURRENT_DIR/cli --request /alarms/monitor > /dev/null
php $CURRENT_DIR/cli --request /alarms/notify > /dev/null
