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
        $errors['database'] = 'Falló el inicio de sesión: ' . $e->getMessage();
    }
  }
}

?>

<div class="login">

  <h1 class="title">Celina</h1>
  <h2 class="subtitle">Control de Asistencia</h2>
  <hr>
  <h4 class="title is-4">Iniciar Sesión</h4>

  <?php if (!empty($errors['database'])): ?>
    <p class="help is-danger"><?php echo $errors['database']; ?></p>
  <?php endif; ?>

  <form method="POST" action="">

  <div class="field">
    <label class="label">Usuario</label>
    <div class="control">
      <input class="input has-background-link-05 <?= isset($errors['usuario']) ? 'is-danger' : ''; ?>" 
        type="text" name="txt_usuario" value="<?= htmlspecialchars($usuario) ?>">
    </div>
    <?php if (!empty($errors['usuario'])): ?>
      <p class="help is-danger"><?php echo $errors['usuario']; ?></p>
    <?php endif; ?>
  </div>

  <div class="field">
    <label class="label">Password</label>
    <div class="control">
      <input class="input has-background-link-05 <?= isset($errors['password']) ? 'is-danger' : ''; ?>" 
        type="password" name="txt_password">
    </div>
    <?php if (!empty($errors['password'])): ?>
      <p class="help is-danger"><?php echo $errors['password']; ?></p>
    <?php endif; ?>
  </div>

  <div class="field">
    <div class="control">
      <button class="button is-block is-fullwidth is-link" type="submmit" name="btn_enviar">Enviar</button>
    </div>
  </div>

  </form>
</div>
<?php include_once 'templates/ifooter.php'; ?>