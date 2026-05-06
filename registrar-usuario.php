<?php
session_start();
// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['logged_in'])) {
  header('Location: index.php');
  exit;
}

include_once 'templates/header.php';
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Inicializar las variables
$descripcion = $usuario = $password = $pwd_hashed = '';
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$descripcion = trim($_POST['txt_descripcion']);
	$usuario = trim($_POST['txt_usuario']);
	$password = trim($_POST['txt_password']);

  // Validar si está vacío
  if (empty($descripcion)) {
      $errors['descripcion'] = 'Campo requerido';
  }
  if (empty($usuario)) {
      $errors['usuario'] = 'Campo requerido';
  }
  if (empty($password)) {
      $errors['password'] = 'Campo requerido';
  } elseif (strlen($password) < '8') {
  		$errors['password'] = 'Debe tener ocho o más caracteres';
  }

	// Crea una clave de hash para una contraseña
	$pwd_hashed = password_hash($password, PASSWORD_DEFAULT);

  // Buscar si ya existe usuario
  if (empty($errors['usuario'])) {
      try {
          $check_query = "SELECT id FROM usuario WHERE username = :usuario";
          $check_stmt = $db->prepare($check_query);
          $check_stmt->bindParam(':usuario', $usuario);
          $check_stmt->execute();

          if ($check_stmt->rowCount() > 0) {
              $errors['usuario'] = 'Ya este usuario existe';
          }
      } catch(PDOException $exception) {
          $errors['database'] = 'Database error: ' . $exception->getMessage();
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
	      }
	  } catch(PDOException $exception) {
	      $errors['database'] = 'Se produjo un error: ' . $exception->getMessage();
	  }
  }	  
}

?>
<p> <a href="home.php">Home</a> | <a href="gestion-usuario.php">Gestión de Usuarios</a> </p>

<h2>Nuevo Usuario</h2>

<hr>

<?php if (!empty($success_message)): ?>
	<p class="exito"><?php echo $success_message; ?></p>
<?php endif; ?>

<?php if (!empty($errors['database'])): ?>
  <p class="problema"><?php echo $errors['database']; ?></p>
<?php endif; ?>

<form method="POST" action="">

	<p>
		<label for="descripcion">Descripción</label>
		<input type="text" name="txt_descripcion" id="descripcion" value="<?= htmlspecialchars($descripcion) ?>">
		<?php if (!empty($errors['descripcion'])): ?>
		  <small class="problema"><?php echo $errors['descripcion']; ?></small>
		<?php endif; ?>
	</p>

	<p>
		<label for="usuario">Nombre de Usuario</label>
		<input type="text" name="txt_usuario" id="usuario" value="<?= htmlspecialchars($usuario) ?>">
		<?php if (!empty($errors['usuario'])): ?>
		  <small class="problema"><?php echo $errors['usuario']; ?></small>
		<?php endif; ?>		
	</p>

	<p>
		<label for="password">Password</label>
		<input type="password" name="txt_password" id="password">
		<?php if (!empty($errors['password'])): ?>
		  <small class="problema"><?php echo $errors['password']; ?></small>
		<?php endif; ?>		
	</p>

	<p>
		<button type="submmit" name="btn_enviar">Enviar</button>
	</p>

</form>


<hr>
<p>
	Bienvenido(a), <?= htmlspecialchars($_SESSION['descripcion']) ?> |
	<a href="logout.php">Cerrar sesión</a> 
</p>

<?php include_once 'templates/footer.php'; ?>