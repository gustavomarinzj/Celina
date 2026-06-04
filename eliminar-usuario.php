<?php
session_start();
// If the user is not logged in, redirect to the login page
if (! isset($_SESSION['logged_in'])) {
  header('Location: index.php');
  exit;
}

include_once 'templates/header.php';
include_once 'config/database.php';
include_once 'config/funciones.php';

$database = new Database();
$db = $database->getConnection();

// Obtener el ID de la URL
$id_usuario = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_usuario <= 0) {
  echo '<div class="column is-6">
        <div class="notification is-warning">
          El <strong>Usuario</strong> no es válido.
        </div>';
  include_once 'templates/footer.php';
  exit;
}

try {
  $query = "SELECT id, descripcion, username, DATE_FORMAT(creado, '%d/%m/%Y') fecha 
            FROM usuario WHERE id = :id";
  $stmt  = $db->prepare($query);
  $stmt->bindParam(':id', $id_usuario);
  $stmt->execute();

  if ($stmt->rowCount() == 0) {
    echo '<div class="column is-6">
          <div class="notification is-warning">
            El <strong>Usuario</strong> no existe.
          </div>';
    include_once 'templates/footer.php';
    exit;
  } else {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $descripcion = $row['descripcion'];
    $usuario = $row['username'];
    $fecha = $row['fecha'];
  }

} catch (PDOException $e) {
    $errors['database'] = 'Se produjo un error: ' . $e->getMessage();
}

// Inicializar las variables
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (! hash_equals($_SESSION['token_csrf'], $_POST['txt_csrf'])) {
    // CSRF attack detected! Log this, redirect, or show an error.
    echo '<div class="column is-6">
          <div class="notification is-danger">
            El <strong>token CSRF</strong> es invalido. Por favor intente más tarde.
          </div>';
    include_once 'templates/footer.php';
    exit();
  }


  try {
    $query = "UPDATE usuario SET
    activo = 0,
    actualizado = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_usuario);

    if ($stmt->execute()) {
      $success_message = '¡Se ha eliminado el usuario!';
      regenerar_token_csrf();

    }
  } catch (PDOException $e) {
      $errors['database'] = 'Se produjo un error: ' . $e->getMessage();
  }
}

?>

<div class="column is-6">

  <h4 class="title is-4">Eliminar Usuario</h4>

  <?php if (! empty($success_message)): ?>
    <div class="notification is-success">
      <?php echo $success_message; ?>
    </div>
  <?php endif; ?>

  <?php if (! empty($errors['database'])): ?>
    <div class="notification is-danger">
      <?php echo $errors['database']; ?>
    </div>
  <?php endif; ?>

<form method="POST" action="">

<?php if (empty($success_message)): ?>

  <article class="message is-warning">
    <div class="message-body">
      <strong>¿Está seguro de eliminar este usuario?</strong>
      Esta acción no se puede deshacer.
      <ul>
        <li><strong>Descripción: </strong><?php echo htmlspecialchars($descripcion); ?></li>
        <li><strong>Usuario: </strong><?php echo htmlspecialchars($usuario); ?></li>
        <li><strong>Fecha de creación: </strong><?php echo htmlspecialchars($fecha); ?></li>
      </ul>
    </div>
  </article>  

  <input type="hidden" name="txt_csrf" value="<?php echo htmlspecialchars($_SESSION['token_csrf']); ?>">

  <div class="field is-grouped is-pulled-right">
    <p class="control">
      <a href="administrar-usuario.php" class="button">Volver</a>
    </p>
  	<p class="control">
  		<button class="button is-danger is-dark" type="submit" name="btn_eliminar">Eliminar</button>
  	</p>
  </div> 

<?php endif; ?>

</form>

</div>
<div class="column is-6"></div>

<?php include_once 'templates/footer.php'; ?>