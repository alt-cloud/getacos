#!/bin/sh

export TMPDIR=$DOCUMENT_ROOT/ALTCOS/tmp

# Split passwd file (/etc/passwd) into
# /usr/etc/passwd - home users password file (uid >= 500)
# /lib/passwd - system users password file (uid < 500)
splitPasswd() {
  frompass=$1
  syspass=$2
  userpass=$3
  > $syspass
  > $userpass
  set -f
  ifs=$IFS
  exec < $frompass
  while read line
  do
    IFS=:;set -- $line;IFS=$ifs
    user=$1
    uid=$3
    if [ $uid -ge 500 -o $user = 'root' -o $user = 'systemd-network' ]
    then
      echo $line >> $userpass
    else
      echo $line >> $syspass
    fi
  done
}

# Split group file (/etc/group) into
# /usr/etc/group - home users group file (uid >= 500)
# /lib/group - system users group file (uid < 500)
splitGroup() {
  fromgroup=$1
  sysgroup=$2
  usergroup=$3
  > $sysgroup
  > $usergroup
  set -f
  ifs=$IFS
  exec < $fromgroup
  while read line
  do
    IFS=:;set -- $line;IFS=$ifs
    user=$1
    uid=$3
    if [ $uid -ge 500 -o $user = 'root' -o $user = 'adm'  -o $user = 'wheel'  -o $user = 'systemd-network'  -o $user = 'systemd-journal'  -o $user = 'docker' ]
    then
      echo $line >> $usergroup
    else
      echo $line >> $sysgroup
    fi
  done
}

# Возвращает тропу, где находятся репозитории bare, archive
# altcos/x86_64/sisyphus -> altcos/x86_64/sisyphus
# altcos/x86_64/Sisyphus/apache -> altcos/x86_64/sisyphus
refRepoDir() {
  ref=$1
  ifs=$IFS
  IFS=/;set -- $ref;IFS=$ifs;
  os=$1;arch=$2;branch=`echo $3 | tr '[:upper:]' '[:lower:]'`
  echo "$os/$arch/$branch";
}


# Возвращает тропу, где находятся данные ветки (vars, roots, ALTCOSfile, ...)
# altcos/x86_64/sisyphus -> altcos/x86_64/sisyphus
# altcos/x86_64/Sisyphus/apache -> altcos/x86_64/sisyphus/apache
refToDir() {
  ref=$1
  echo $ref | tr '[:upper:]' '[:lower:]'
}

# Возвращает имя поддиректория варианта в каталоге /vars
# sisyphus.20210914.0.0 => 20210914/0/0
# sisyphus_apache.20210914.0.0 => 20210914/0/0
versionVarSubDir() {
  version=`echo $1 | tr '[:upper:]' '[:lower:]'`
  ifs=$IFS
  IFS=.;set -- $version;IFS=$ifs
  date=$2
  major=$3
  minor=$4
  echo "$date/$major/$minor"
}

fullCommitId() {
  (
  refDir=$1
  shortCommitId=$2
  VarDir=$DOCUMENT_ROOT/ALTCOS/streams/$refDir/vars
  cd $VarDir
  ids=`ls -1dr $shortCommitId*`
  set -- $ids
  if [ $# -eq 0 ]
  then
    echo "Коммит $shortCommitId отсутствует" >&2
    echo ''
    return
  fi
  if [ $# -gt 1 ]
  then
    echo "Коммит $shortCommitId неоднозначен. Ему соответствуют несколько коммитов: $*" >&2
    echo ''
    return
  fi
  ret=$1
  echo $ret
  )
}

lastCommitId() {
  (
  refDir=$1
  cd $DOCUMENT_ROOT/ALTCOS/streams/$refDir/vars
  id=`ls -1tdr ???????????????????????????????????????????????????????????????? | tail -1`
  echo $id
  )
}


# Возвращает имя потока
# altcos/x86_64/Sisyphus/apache -> sisyphus
refStream() {
  ref=$1
  refDir=`refToDir $ref`
  ifs=$IFS;IFS=/;set -- $refDir;IFS=$ifs
  shift;shift
  stream=$1
  echo $stream
}

# Является ли указанная ветка базовой
isBaseRef() {
  ref=$1
  ifs=$IFS
  IFS=/;set -- $ref;IFS=$ifs
  if [ $# -eq 3 ]
  then
    return 0
  else
    return 1
  fi
}

# Возвращает версию по имени ветки и $commitId
# altcos/x86_64/sisyphus  00156 -> sisyphus.$date.$major.$minor
refVersion() {
  (
  ref=$1
  commitId=$2
  refRepoDir=`refRepoDir $ref`
  repoBarePath="$DOCUMENT_ROOT/ALTCOS/streams/$refRepoDir/bare/repo";
  ret=`ostree --repo=$repoBarePath show $commitId --print-metadata-key=version | tr -d "'"`
#   refDir=`refToDir $ref`
#   VarDir=$DOCUMENT_ROOT/ALTCOS/streams/$refDir/vars
#   fullCommitId=`fullCommitId $refDir $commitId`
#   cd $VarDir
#   ifs=$IFS;IFS=/;set -- `readlink $fullCommitId`;IFS=$ifs
#   date=$1
#   major=$2
#   minor=$3
# #   refDir=`refToDir $ref`
#   IFS=/;set -- $refDir;IFS=$ifs
#   shift;shift
#   stream=$1
#   shift
#   while [ $# -gt 0 ]
#   do
#     stream="$stream_$1"
#     shift
#   done
#   ret="$stream.$date.$major.$minor"
  echo $ret
  )
}


function checkAptDirs() {
  rootDir=$1
  sudo mkdir -p $rootDir/var/lib/apt/lists/partial $rootDir/var/lib/apt/prefetch/
  sudo mkdir -p $rootDir/var/cache/apt/archives/partial $rootDir/var/cache/apt/gensrclist $rootDir/var/cache/apt/genpkglist
  sudo chmod -R 770 $rootDir/var/cache/apt/
  sudo chmod -R g+s $rootDir/var/cache/apt/
  sudo chown root:rpm $rootDir/var/cache/apt/
}


