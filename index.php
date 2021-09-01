<?php
//phpinfo();
$rootdir = $_SERVER['DOCUMENT_ROOT'];
ini_set('include_path', "$rootdir");
require_once('functions.php');
?>

<html>
<head>
<title>Links</title>
</head>
<body>

<h1>TEST</h1>

<?php
$streams = listStreams();
echo "<pre>STREAMS=" . print_r($streams, 1) . "</pre>\n"; 
foreach ($streams as $stream) {
?>
<ul><h2>Поток: <?= $stream?></h2>
<?php
  $refs = getRefs($stream);
  foreach ($refs  as $ref) {
?>
  <li>
    <ul><h3>REF: <?= $ref?></h3>
    <li><a href='http://<?= $_SERVER['HTTP_HOST']?>/v1/graph/?stream=<?= $stream?>&basearch=x86_64' target='ostreeREST'>ГРАФ</a></li>
<?php
    $commits = getCommits($repo, $ref);      
?>
    </ul>
  </li>
<?php
  }
?>
</ul>
<?php
}
?>



<h2>Sisyphus</h2>

<h3>Граф</h3>

<ul>
<li><a href='http://getacos.altlinux.org/v1/graph/?stream=sisyphus&basearch=x86_64'>Graph</a></li>
<li><a href='http://getacos.altlinux.org/ostree/update/?ref=acos/x86_64/sisyphus&version=sisyphus.20210830.0.0'>Update</a></li>
</ul>

<h3>OSTREE</h3>




<h2>p10</h2>
<ul>
<li>
</ul>

<h1>ACOS Clients</h1>

<h2>Sisyphus</h2>
<ul>
<li>
</ul>

<h2>p10</h2>
<ul>
<li>
</ul>
