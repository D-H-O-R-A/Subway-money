<?php
session_start();

if (!isset($_SESSION['emailadm'])) {
    header("Location: ./login");
    echo 'não logado';
    exit();
}