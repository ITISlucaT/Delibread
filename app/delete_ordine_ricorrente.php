<?php 
session_start(); 

if (!isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit();
}

include 'conf/db_config.php'; 
include 'templates/header.php';  



$stmt = $conn->prepare("DELETE FROM ordine_ricorrente WHERE IdOrdineRicorrente = ?");
$stmt->execute([$_GET['id']]);
$conn->close();


header("Location: ordini_ricorrenti.php");
















?>