<?php
class repos {

  static $OSs = ['acos' => 'ALTLinux Container OS'];

  static function listOSs() {
    $ret = array_keys(repos::OSs);
    return $ret;
  }

  static function getOSName($os) {
    $ret = repos::$OSs[$os];
    return $ret;
  }

  static function listArchs() {
    $fd = opendir($_SERVER['DOCUMENT_ROOT'] . "/ACOS/streams/acos/");
    $ret = [];
    while ($entry=readdir($fd)) {
      if (substr($entry,0,1) == '.') continue;
      $ret[] = $entry;
    }
    return $ret;
  }

  static function listStreams($arch='x86_64') {
    $fd = opendir($_SERVER['DOCUMENT_ROOT'] . "/ACOS/streams/acos/$arch");
    $ret = [];
    while ($entry=readdir($fd)) {
      if (substr($entry,0,1) == '.') continue;
      $ret[] = $entry;
    }
    return $ret;
  }

  static function repoTypes() {
    $ret = ['bare', 'archive'];
    return $ret;
  }

  /*
   * Возвращает true, усли ref базовый: acos/x86_64/sisyphus
   */
  static  function isBaseRef($ref) {
    $ret = count(explode('/', $ref)) == 3;
    return $ret;
  }

    /*
   * Формирует имя подветки
   * переводя в верхний регистр первую букву текущей ветки и
   * добавляя через / имя подветки
   * subRef('acos/x86_64/sisyphus', 'apache') => acos/x86_64/Sisyphus/apache
   */
  static function subRef($ref, $subName) {
    $path = explode('/', $ref);
    $lastN = count($path) - 1;
    $path[$lastN] = ucfirst($path[$lastN]);
    $path[] = $subName;
    $ret = implode('/', $path);
    return $ret;
  }

  /**
   * Возвращает тропу, где находятся репозитории bare, archive
   * acos/x86_64/sisyphus -> acos/x86_64/sisyphus
   * acos/x86_64/Sisyphus/apache -> acos/x86_64/sisyphus
   */
  static function refRepoDir($ref) {
    $path = array_slice(explode('/', $ref), 0, 3);
    $path[2] = strtolower($path[2]);
    $ret = implode('/', $path);
    return $ret;
  }

  /*
   * Возвращает вариант ветки
   * acos/x86_64/Sisyphus/apache -> sisyphus_apache.$date.$major.$minor
   */
  static function refVersion($ref, $date=false, $major=0, $minor=0) {
    if (!$date) {
      $date = strftime("%Y%m%d");
    }
    $path = explode('/', strtolower($ref));
    $stream = implode('_', array_slice($path, 2));
    $ret = "$stream.$date.$major.$minor";
    return $ret;
  }

  /*
   * Возвращает имя поддиректория дат ветки в каталоге $ref/vars/...
   * acos/x86_64/Sisyphus => ./
   * acos/x86_64/Sisyphus/apache => ./apache
   */
  static function refVersionDatesSubDir($ref) {
    $path = explode('/', strtolower($ref));
    $ret = './' . implode('/', array_slice($path, 3));
    return $ret;
  }


  /*
   * Возвращает имя поддиректория варианта в каталоге /vars
   * sisyphus.20210914.0.0 => 20210914/0/0
   * sisyphus_apache.20210914.0.0 => apache/20210914/0/0
   */
  static function versionVarSubDir($version) {
    $path = explode('.', strtolower($version));
    $stream = $path[0];
    $path1 = explode('_', $stream);
    $dir = implode('_', array_slice($path1, 1));
    $date = $path[1];
    $major = $path[2];
    $minor = $path[3];
    $ret = "$dir/$date/$major/$minor";
    return $ret;
  }




}
