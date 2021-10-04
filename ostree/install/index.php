<?php
$rootdir = $_SERVER['DOCUMENT_ROOT'];
ini_set('include_path', "$rootdir/class");
require_once('repo.php');
require_once('refsConf.php');
require_once('log.php');

//MAIN
$startTime = time();
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
putenv("DOCUMENT_ROOT=$DOCUMENT_ROOT");
$BINDIR = "$DOCUMENT_ROOT/ostree/bin";
$ref = $_REQUEST['ref'];
$subName = $_REQUEST['subName'];
$pkgs = $_REQUEST['pkgs'];
$refDir = repos::refRepoDir($ref);

$subRef = repos::subRef($ref, $subName);
$subVersion = repos::refVersion($subRef);
// $subVersionVarSubDir = repos::versionVarSubDir($subVersion);
$log = new log('install');

$repoType = 'bare';
$repo = new repo($ref, $repoType);
// $refRepoDir = $repo->refRepoDir;

if (!$repo->haveConfig()) {
  $log->write("Bare repository $repoBarePath don't exists\n");
  exit(1);
}

$commits = $repo->getCommits($ref);
$lastCommitId = $repo->lastCommitId;
$lastCommit = $repo->lastCommit;
# $log->write("\nlastCommitId=$lastCommitId lastCommit=" . print_r($lastCommit, 1) . "\n");
$lastVersion = $lastCommit['Version'];
// $versionVarSubDir = repos::versionVarSubDir($lastVersion);

$cmd = "$BINDIR/ostree_checkout.sh '$ref' '$lastCommitId' '$subRef'";
$log->write("CHECKOUTCMD=$cmd\n");
$output = [];
exec($cmd, $output);
$log->write("CHECKOUT=\n" . implode("\n",$output) . "\n");

$cmd = "$BINDIR/apt-get_update.sh $subRef";
$log->write("APT-GET_UPDATETCMD=$cmd\n");
$output = [];
exec($cmd, $output);
$log->write("APT-GET_UPDATE=\n" . implode("\n",$output). "\n");

$cmd = "$BINDIR/apt-get_install.sh $subRef $pkgs";
$log->write("APT-GET_INSTALL=$cmd\n");
$output = [];
exec($cmd, $output);
$log->write("APT-GET_INSTALL=\n" . implode("\n",$output). "\n");

$cmd = "$BINDIR/syncUpdates.sh $subRef $lastCommitId $subVersion";
$log->write("SYNCUPDATESCMD=$cmd\n");
$output = [];
exec($cmd, $output);
$log->write("SYNCUPDATES=\n" . implode("\n",$output). "\n");

$rpmList = $repo->rpmList($subVersion);
$refsConf = new refsConf($subRef, $subVersion, $pkgs);
$refsConf->addRpmList($rpmList);
$refsConf->save();

$cmd = "$BINDIR/ostree_commit.sh $subRef $lastCommitId $subVersion";
$log->write("COMMITCMD=$cmd\n");
$output = [];
exec($cmd, $output);
$log->write("COMMIT=\n" . implode("\n",$output). "\n");
$ret = $repo->cmpRPMs($subVersion, $lastVersion, $ref);
echo json_encode($ret, JSON_PRETTY_PRINT);
