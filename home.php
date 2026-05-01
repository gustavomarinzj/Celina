<?php
session_start();
// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['logged_in'])) {
  header('Location: index.php');
  exit;
}

include_once 'templates/header.php' 

?>

<hr>

<div class="alert info">
Lorem ipsum dolor sit amet consectetur adipiscing elit. Consectetur adipiscing elit quisque faucibus ex sapien vitae. Ex sapien vitae pellentesque sem placerat in id. Placerat in id cursus mi pretium tellus duis. Pretium tellus duis convallis tempus leo eu aenean.
</div>

<hr>

<p> 
  Bienvenido(a), <?= htmlspecialchars($_SESSION['descripcion']) ?> |

  <?php if ($_SESSION['rol'] == 'administrador') {
    echo '<a href="gestion-usuario.php">Gestión de usuarios</a> | ';
  } ?> 

  <a href="logout.php">Cerrar sesión</a> 
</p>

<?php include_once 'templates/footer.php'; ?>