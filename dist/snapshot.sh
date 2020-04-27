#!/bin/bash

#
# snapshot.sh - coWiki nightly snapshots builder
#
# This script is for maintainer use only, so do not care if it does not
# work for you. Crontab usage sample for snapshots:
#
# 0 7 * * * root /path/to/your/cowiki/dist/snapshot.sh
#
# chown 755 this file to make it executable.
#
# (C) Sep 02, 2003 Daniel T. Gorski, <daniel.gorski@develnet.org>
#
# $Id: snapshot.sh,v 1.5 2005/02/19 21:26:21 dgorski Exp $
#

DOCR='/path/to/snaps/vhost/'
FIND=/usr/bin/find

$DOCR/dist/buildpackage.sh HEAD > /dev/null
$FIND $DOCR/dist -name "cowiki*" -mtime +21 -exec rm {} \; 2> /dev/null