#!/bin/bash

#
# buildpackage.sh - coWiki distribution package builder
#
# This script is for maintainer use only, so do not care if it does not
# work for you. Crontab usage sample for snapshots:
#
# 0 7 * * * root /path/to/your/cowiki/dist/buildpackage.sh HEAD > /dev/null
#
# chown 755 this file to make it executable. If you want to create CVS
# snapshots only (buildpackage.sh HEAD) and get rid of older snapshots,
# use the snapshot.sh script which keeps the directory clean.
#
# (C) Sep 02, 2003 Daniel T. Gorski, <daniel.gorski@develnet.org>
#                  Kai Schroeder, <k.schroeder@php.net>
#
# $Id: buildpackage.sh,v 1.13 2005/02/24 00:31:40 paulha Exp $
#

DOCR='/path/to/snaps/vhost/'

CAT=/bin/cat
GREP=/bin/grep
SED=/bin/sed
CUT=/bin/cut
AWK=/bin/awk
HEAD=/usr/bin/head

# -------------------------------------------------------------------------

function headline {
  echo
  echo "coWiki package builder, administrative tool for coWiki distribution"
  echo "Version 1.0, Daniel T. Gorski / Kai Schroeder"
  echo
}

function usage {
  headline
  echo "This script exports coWiki from its CVS repository and builds"
  echo "a tar package for distribution."
  echo
  echo "Usage:"
  echo "  $0 cvstag targetdir user group mode"
  echo
  echo "Where cvstag is the coWiki release tag like e.g. REL_X_X_X. Using"
  echo "the HEAD cvstag will create a snapshot with current date (used for"
  echo "nightly builds). user, group and mode defines the owner and"
  echo "permissions of the tar archive."
  echo
}

function cleanup {
  rm -rf cowiki 2> /dev/null
  rm -f error.txt 2> /dev/null
}

function abort {
  echo -n "Error: "
  cat error.txt 2> /dev/null
  cleanup
  echo "Aborted."
  exit 1
}

function failed {
  echo " failed."
}

function ok {
  echo " done."
}

function checkerror {
  if [ -f error.txt ]; then
    SIZE=`du -b error.txt | cut -b 1`

    if [ $SIZE != "0" ]; then
      failed; abort
    fi

  fi
  ok
}

# --- Here we go ----------------------------------------------------------

if [ "$1" = "" ]; then
  usage
  exit 1
fi

if [ "$2" = "" ]; then
  TARGET=$DOCR/dist
else
  TARGET="$2"
fi

if [ "$3" = "" ]; then
  OWNER="wwwrun"
else
  OWNER="$3"
fi

if [ "$4" = "" ]; then
  GROUP="nogroup"
else
  GROUP="$4"
fi

if [ "$5" = "" ]; then
  MODE="644"
else
  MODE="$5"
fi

# -------------------------------------------------------------------------

headline
echo -n "Preventive clean up ..."
cleanup
ok

# -------------------------------------------------------------------------

echo -n "Exporting revision '"
echo -n $1
echo -n "' from coWiki repository ..."
cvs -Q -d :pserver:anoncvs@cvs.tigris.org:/cvs \
  export -r $1 cowiki 2> error.txt
checkerror

# -------------------------------------------------------------------------

echo -n "Removing 'cowiki/dist/' directory ..."
rm -rf cowiki/dist/ 2> /dev/null
ok

# -------------------------------------------------------------------------

echo -n "Removing 'cowiki/TODO' file ..."
rm -rf cowiki/TODO 2> /dev/null
ok

# -------------------------------------------------------------------------

echo -n "Removing 'cowiki/www/' directory ..."
rm -rf cowiki/www/ 2> /dev/null
ok

# -------------------------------------------------------------------------

# Snapshot from HEAD
RELEASE=`echo "$1" | /bin/sed s/_/./g | /bin/sed s/REL.//g`

MODIFIED=`find ./cowiki -type f -exec stat "-c %y" "{}" \; | sort | tail -1`
MODIFIED_STAMP=`echo $MODIFIED | sed 's#.*\([12]...\)-\(..\)-\(..\) \(..\):\(..\):\(..\).*#\1\2\3\4\5.\6#'`

if [ $RELEASE = "HEAD" ]; then
  MAJOR=`$GREP \(\'COWIKI_VERSION_MAJOR\' cowiki/htdocs/version.php \
         | $SED s/[^0-9]//g`
  MINOR=`$GREP \(\'COWIKI_VERSION_MINOR\' cowiki/htdocs/version.php \
         | $SED s/[^0-9]//g`
  MICRO=`$GREP \(\'COWIKI_VERSION_MICRO\' cowiki/htdocs/version.php \
         | $SED s/[^0-9]//g`
  PATCH=`$GREP \(\'COWIKI_VERSION_PATCH\' cowiki/htdocs/version.php \
         | $HEAD -1 | $SED s/.*,\ *\'// | $SED s/\'.*//`

  VERSION=$MAJOR.$MINOR.$MICRO$PATCH

  MODIFIED_RELEASE=`echo $MODIFIED | sed 's#.*\([12]...\)-\(..\)-\(..\) \(..\):\(..\):\(..\).*#\1-\2-\3-\4\5#'`

  RELEASE=$VERSION-$MODIFIED_RELEASE
fi

echo -n "Renaming source directory ..."
mv cowiki cowiki-$RELEASE 2> error.txt
checkerror

# -------------------------------------------------------------------------

echo -n "Creating tar.gz ..."
tar -czf cowiki-$RELEASE.tar.gz cowiki-$RELEASE 2> error.txt
touch -t $MODIFIED_STAMP cowiki-$RELEASE.tar.gz
checkerror

# -------------------------------------------------------------------------

echo -n "Cleaning up ..."
rm -rf cowiki-$RELEASE 2> /dev/null
rm -f error.txt 2> /dev/null

chown $OWNER.$GROUP cowiki-$RELEASE.tar.gz 2> /dev/null
chmod $MODE cowiki-$RELEASE.tar.gz
mv cowiki-$RELEASE.tar.gz $TARGET 2> /dev/null
ok

echo "Finished."
echo
echo "Package cowiki-$RELEASE.tar.gz has been created and moved to $TARGET/"
echo
exit 0
