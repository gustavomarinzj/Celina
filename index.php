<?php
session_start();

if (isset($_SESSION['logged_in'])) {
    header('Location: home.php');
    exit;
  }

include_once 'templates/iheader.php';
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$usuario = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$usuario = trim($_POST['txt_usuario']);
	$password = trim($_POST['txt_password']);

  // Validar si está vacío
  if (empty($usuario)) {
      $errors['usuario'] = 'Campo requerido';
  }
  if (empty($password)) {
      $errors['password'] = 'Campo requerido';
  }

  // Buscar si ya existe usuario
  if (empty($errors['usuario'])) {
    try {
      $query = "SELECT id, descripcion, username, rol, password FROM usuario WHERE username = :usuario";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':usuario', $usuario);
      $stmt->execute();

      if ($stmt->rowCount() === 0) {
      		$errors['usuario'] = 'Este usuario no existe';
      } else {
        $user = $stmt->fetch();

        // Verify password
        if (!password_verify($password, $user['password'])) {
          $errors['password'] = 'Usuario o password incorrecto';
        } else if (empty($errors)) {

          // Regenerate session ID for security
          session_regenerate_id(true);

          // Set session variables
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['descripcion'] = $user['descripcion'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['rol'] = $user['rol'];
          $_SESSION['logged_in'] = true;

          header('location: home.php');
        }        
      }
    } catch(PDOException $e) {
        $errors['database'] = 'Login failed: ' . $e->getMessage();
    }
  }
}

?>

<h2>Iniciar sesión</h2>

<?php if (!empty($errors['database'])): ?>
  <p class="problema"><?php echo $errors['database']; ?></p>
<?php endif; ?>

<form method="POST" action="">
	
	<p>
		<label for="usuario">Usuario</label>
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

<?php include_once 'templates/ifooter.php'; ?>