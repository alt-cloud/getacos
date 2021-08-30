#!/bin/sh

if [ $UID -eq 0 ]
then
	echo "Can't run as superuser "
	exit 1
fi

if [ $# -ne 1 -o "$1" != 'sisyphus' -a $1 != 'p10' ]
then
	echo "Format: $0 sisyphus|p10"
	exit 1
fi
BRANCH=$1

if [ $BRANCH = 'sisyphus' ]
then
	REPOBRANCH='Sisyphus'
else
	REPOBRANCH=$BRANCH
fi

if [ -z "$DOCUMENT_ROOT" ]
then
	echo "Variable DOCUMENT_ROOT must be defined"
	exit 1
fi
export IMAGEDIR="$DOCUMENT_ROOT/ACOS/rootfs_archives/acos/x86_64/$BRANCH/"

if [ ! -d $IMAGEDIR ]
then
	echo "rootfs_archive directory $IMAGEDIR must exists"
	echo "Run under root: mkdir -p  -m 777 $IMAGEDIR"
	exit 1
fi

MKIMAGEDIR="$DOCUMENT_ROOT/../mkimage-profiles"
if [ ! -d $MKIMAGEDIR ]
then
        echo "mkimage-profiles directory $MKIMAGEDIR must exists"
        exit 1
fi

APTDIR="~/apt"
if [ ! $APTDIR ]
then
	mkdir -p ~/apt/
fi
if [ ! -f  ~/apt/lists/partial ]
then
	mkdir -p ~/apt/lists/partial
fi
if [ ! -f ~/apt/cache/$BRANCH/archives/partial ]
then
	mkdir -p ~/apt/cache/$BRANCH/archives/partial
fi
if [ ! -f ~/apt/apt.conf.$BRANCH.x86_64 ]
then
	cat <<EOF > ~/apt/apt.conf.$BRANCH.x86_64
Dir::Etc::SourceList "$HOME/apt/sources.list.$BRANCH.x86_64";
Dir::Etc::SourceParts /var/empty;
Dir::Etc::main "/dev/null";
Dir::Etc::parts "/var/empty";
APT::Architecture "64";
Dir::State::lists "$HOME/apt/lists/";
Dir::Cache "$HOME/apt/cache/$BRANCH/";
EOF
fi
if [ ! -f ~/apt/sources.list.$BRANCH.x86_64 ]
then
	cat <<EOF > ~/apt/sources.list.$BRANCH.x86_64
rpm [alt] http://ftp.altlinux.org/pub/distributions/ALTLinux/ $REPOBRANCH/x86_64 classic
rpm [alt] http://ftp.altlinux.org/pub/distributions/ALTLinux/ $REPOBRANCH/noarch classic
EOF
fi
cd $MKIMAGEDIR
make DEBUG=1 APTCONF=~/apt/apt.conf.$BRANCH.x86_64 BRANCH=$BRANCH ARCH=x86_64 vm/acos.tar