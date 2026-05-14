<?php
session_start();
// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['logged_in'])) {
  header('Location: index.php');
  exit;
}

include_once 'templates/header.php' 
?>

<?php include_once 'templates/footer.php'; ?>