#!/bin/sh
CURRENT_DIR=`dirname "$0"`
php $CURRENT_DIR/cli domains heartbeats > /dev/null
php $CURRENT_DIR/cli alarms monitor > /dev/null
php $CURRENT_DIR/cli alarms notify > /dev/null

[ $(( $RANDOM % 60 )) == 0 ] && php $CURRENT_DIR/cli system clear-old > /dev/null
