<?php
/**
 * File: _ide_helper.php
 * VERSI PAKSA (Untuk menghilangkan error: Assigning "void" from a function)
 */

// Kita tidak pakai if(false) kali ini, tapi kita pastikan file ini tidak di-include di mana pun.
// Cukup ada di folder project saja.

/** @return resource|false */
function oci_connect($u, $p, $db = null, $c = null, $m = null) { return imagecreate(1,1); }

/** @return resource|false */
function oci_parse($conn, $sql) { return imagecreate(1,1); }

/** @return bool */
function oci_execute($stmt, $m = 32) { return true; }

/** @return array|false */
function oci_fetch_array($stmt, $m = null) { return []; }

/** @return bool */
function oci_bind_by_name($stmt, $bv, &$var, $max = -1, $type = 1) { return true; }

/** @return int */
function oci_num_rows($stmt) { return 0; }

/** @return bool */
function oci_commit($conn) { return true; }

/** @return array|false */
function oci_error($src = null) { return []; }

define('OCI_ASSOC', 1);
define('OCI_COMMIT_ON_SUCCESS', 32);