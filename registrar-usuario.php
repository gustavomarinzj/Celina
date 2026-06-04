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

// Inicializar las variables
$descripcion = $usuario = $password = $pwd_hashed = '';
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (! hash_equals($_SESSION['token_csrf'], $_POST['txt_csrf'])) {
    // CSRF attack detected! Log this, redirect, or show an error.
    echo '<div class="column is-6">
          <div class="notification is-warning">
            El <strong>token CSRF</strong> es invalido. Por favor intente más tarde.
          </div>';
    include_once 'templates/footer.php';
    exit();
  }

  $descripcion = trim($_POST['txt_descripcion']);
  $usuario = trim($_POST['txt_usuario']);
  $password = trim($_POST['txt_password']);

    // Validar
  if (empty($descripcion)) {
    $errors['descripcion'] = 'Campo requerido';
  }
  if (empty($usuario)) {
    $errors['usuario'] = 'Campo requerido';
  }
  if (empty($password)) {
    $errors['password'] = 'Campo requerido';
  } elseif (! preg_match('/^(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{6,}$/', $password)) {
    $errors['password'] = 'Password no cumple con el formato';
  }

  // Crea una clave de hash para una contraseña
  $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);

  // Buscar si ya existe usuario
  if (empty($errors['usuario'])) {
    try {
      $check_query = "SELECT id FROM usuario WHERE username = :usuario";
      $check_stmt  = $db->prepare($check_query);
      $check_stmt->bindParam(':usuario', $usuario);
      $check_stmt->execute();

      if ($check_stmt->rowCount() > 0) {
        $errors['usuario'] = 'Ya este usuario existe';
      }
    } catch (PDOException $e) {
      $errors['database'] = 'Database error: ' . $e->getMessage();
    }
  }
  // Si no hay errores insert en la bd
  if (empty($errors)) {
    try {
      $query = "INSERT INTO usuario (descripcion, username, password)
      VALUES (:descripcion, :usuario, :password)";

      $stmt = $db->prepare($query);
      $stmt->bindParam(':descripcion', $descripcion);
      $stmt->bindParam(':usuario', $usuario);
      $stmt->bindParam(':password', $pwd_hashed);

      if ($stmt->execute()) {
        $success_message = '¡Registro exitoso!';
        $descripcion = $usuario = $password = $pwd_hashed = '';
        regenerar_token_csrf();
      }
    } catch (PDOException $e) {
      $errors['database'] = 'Se produjo un error: ' . $e->getMessage();
    }
  }
}

?>
<div class="column is-6">

  <h4 class="title is-4">Registrar Usuario</h4>

  <?php if (! empty($success_message)): ?>
    <div class="notification is-success">
        <?php echo $success_message; ?>
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
      <input class="input <?php echo isset($errors['descripcion']) ? 'is-danger' : ''; ?>" type="text" name="txt_descripcion"
      value="<?php echo htmlspecialchars($descripcion) ?>">
      <?php if (! empty($errors['descripcion'])): ?>
       <p class="help is-danger"><?php echo $errors['descripcion']; ?></p>
     <?php endif; ?>
   </div>
 </div>

 <div class="field">
   <label class="label">Nombre de Usuario</label>
   <input class="input <?php echo isset($errors['usuario']) ? 'is-danger' : ''; ?>" type="text" name="txt_usuario"
   value="<?php echo htmlspecialchars($usuario) ?>">
   <?php if (! empty($errors['usuario'])): ?>
    <p class="help is-danger"><?php echo $errors['usuario']; ?></p>
  <?php endif; ?>
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
		<button class="button is-link is-pulled-right" type="submit" name="btn_enviar">Enviar</button>
	</div>
</div>

</form>

</div>
<div class="column is-6"></div>

<?php include_once 'templates/footer.php'; ?>