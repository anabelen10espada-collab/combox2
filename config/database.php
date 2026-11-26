<?php

$supa_host     = "aws-0-ca-central-1.pooler.supabase.com";
$supa_port     = "6543";
$supa_user     = "postgres.atlffzzvhifphuexjnlp";
$supa_dbname   = "postgres";
$supa_password = "unicesmag@@";

$supa_conn = pg_connect("
  host=$supa_host
  port=$supa_port
  dbname=$supa_dbname
  user=$supa_user
  password=$supa_password
  sslmode=require
");

if (!$supa_conn) {
    die("Error de conexión con Supabase.");
}