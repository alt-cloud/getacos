<?php
// phpinfo(); exit(0);
$rootdir = $_SERVER['DOCUMENT_ROOT'];
ini_set('include_path', "$rootdir/class");
require_once('repo.php');
$ref = $_REQUEST['ref'];
// $repoType = 'bare';
$repo = new repo($ref, 'bare');
if (!$repo->haveConfig()) {
        echo "Bare repository $repoBarePath don't exists";
        exit(1);
}

$archiveName =  $_REQUEST['archiveName'];
$archiveRepo = new repo($ref, 'archive', $archiveName);
if (!$archiveRepo->haveConfig()) {
  $archiveRepo->init();
}




$commits = $repo->getCommits($ref);
$lastCommitId = $repo->lastCommitId;

$cmd = "sudo ostree pull-local --depth=-1 " . $repo->repoDir . " $ref $lastCommitId --repo=". $archiveRepo->repoDir .' 2>&1';;
$output = [];
echo "<pre>CMD OSTREE PULL MIRROR=$cmd</pre>\n";
exec($cmd, $output);
echo "<pre>OUTPUT OSTREE PULL MIRROR=" . print_r($output, 1) . "</pre>\n";

// $commitIds = array_keys($commits);
//
// foreach ($commitIds as $commitId) {
//     $cmd = "sudo ostree pull-local " . $repo->repoDir . " $ref $commitId --repo=".$archiveRepo->repoDir .' 2>&1';;
//     $output = [];
//     echo "<pre>CMD=$cmd</pre>\n";
//     exec($cmd, $output);
//     echo "<pre>REFS=" . print_r($output, 1) . "</pre>\n";
// }

$refFile = $archiveRepo->repoDir . "/refs/heads/" . $archiveRepo->ref;

$cmd = "sudo echo $lastCommitId | sudo  tee $refFile";
// $cmd = "sudo cat >$refFile <<EOF\n$lastCommitId\nEOF\n";
echo "<pre>CMD=$cmd</pre>\n";
$output = [];
exec($cmd, $output);
echo "<pre>RES=" . print_r($output, 1) . "</pre>\n";
