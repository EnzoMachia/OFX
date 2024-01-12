<?php
$hostname = "localhost";
$bancodedados = "ofx";
$usuario = "root";
$senha = "";

$mysqli = new mysqli( $hostname, $usuario, $senha, $bancodedados);

if ($mysqli->connect_error) {
    die("Connection failed: ". $mysqli->connect_error);
}