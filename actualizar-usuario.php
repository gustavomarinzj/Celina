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
  echo '<div class="notification is-warning">El <strong>Usuario</strong> no es válido.</div>';
  include_once 'templates/footer.php';
  exit;
}

try {
  $query = "SELECT * FROM usuario WHERE id = :id";
  $stmt  = $db->prepare($query);
  $stmt->bindParam(':id', $id_usuario);
  $stmt->execute();

  if ($stmt->rowCount() == 0) {
    echo '<div class="notification is-warning">El <strong>Usuario</strong> no existe.</div>';
    include_once 'templates/footer.php';
    exit;
  } else {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $descripcion = $row['descripcion'];
    $usuario = $row['username'];
  }

} catch (PDOException $e) {
  echo '<div class="notification is-danger">Error: ' . $e->getMessage() . '</div>';
  include_once 'includes/footer.php';
  exit;
}

// Inicializar las variables
$password = '';
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (! hash_equals($_SESSION['token_csrf'], $_POST['txt_csrf'])) {
    // CSRF attack detected! Log this, redirect, or show an error.
    echo '<div class="notification is-warning">
    El <strong>token CSRF</strong> es invalido. Por favor intente más tarde.</div>';
    include_once 'templates/footer.php';
    exit();
  }

  $password = trim($_POST['txt_password']);

  // Validar
  if (empty($password)) {
    $errors['password'] = 'Campo requerido';
  } elseif (! preg_match('/^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{6,}$/', $password)) {
    $errors['password'] = 'Password no cumple con el formato';
  }

  // Crea una clave de hash para una contraseña
  $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);

  // Si no hay errores insert en la bd
  if (empty($errors)) {
    try {
      $query = "UPDATE usuario SET
      password = :password,
      actualizado = CURRENT_TIMESTAMP
      WHERE id = :id";

      $stmt = $db->prepare($query);
      $stmt->bindParam(':password', $pwd_hashed);
      $stmt->bindParam(':id', $id_usuario);

      if ($stmt->execute()) {
        $success_message = '¡Password actualizado!';
        $password = $pwd_hashed = '';
        regenerar_token_csrf();
      }
    } catch (PDOException $e) {
      $errors['database'] = 'Se produjo un error: ' . $e->getMessage();
    }
  }
}

?>

<div class="column is-6">

  <h4 class="title is-4">Actualizar Usuario</h4>
  <h6 class="subtitle is-6">Cambiar password</h6>

  <?php if (! empty($success_message)): ?>
    <div class="message is-success">
      <div class="message-body">
        <?php echo $success_message; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (! empty($errors['database'])): ?>
    <div class="message is-danger">
      <div class="message-body">
        <?php echo $errors['database']; ?>
      </div>
    </div>
  <?php endif; ?>

  <form method="POST" action="">

    <div class="field">
     <label class="label">Descripción</label>
     <div class="control">
      <input class="input" type="text" name="txt_descripcion"
      value="<?php echo htmlspecialchars($descripcion) ?>" disabled>
    </div>
  </div>

  <div class="field">
   <label class="label">Nombre de Usuario</label>
   <input class="input" type="text" name="txt_usuario"
   value="<?php echo htmlspecialchars($usuario) ?>" disabled>
 </div>

 <div class="field">
   <label class="label">Password</label>
   <input class="input <?php echo isset($errors['password']) ? 'is-danger' : ''; ?>" type="password" name="txt_password">
   <?php if (! empty($errors['password'])): ?>
    <p class="help is-danger"><?php echo $errors['password']; ?></p>
  <?php endif; ?>
</div>

<article class="message is-info is-small">
  <div class="message-body content">
  	<strong>La contraseña debe tener:</strong>
  	<ul>
  		<li>Más de seis caracteres</li>
  		<li>Al menos un dígito</li>
  		<li>Al menos una minúscula y al menos una mayúscula</li>
  		<li>NO puede tener otros símbolos</li>
  	</ul>
  </div>
</article>

<input type="hidden" name="txt_csrf" value="<?php echo htmlspecialchars($_SESSION['token_csrf']); ?>">

<div class="field">
	<div class="control">
		<button class="button is-link" type="submit" name="btn_enviar">Enviar</button>
	</div>
</div>

</form>

</div>
<div class="column is-6"></div>

<?php include_once 'templates/footer.php'; ?>