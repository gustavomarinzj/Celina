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

try {
  $query = "SELECT id, descripcion, username, rol FROM usuario ORDER BY descripcion ASC";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $usuarios = $stmt->fetchAll();  

} catch (PDOException $e) {
	$error_message = "Error: " . $e->getMessage();
}


?>

<p>	<a href="home.php">Home</a> </p>

<h2>Gestión de usuarios</h2>

<hr>

<p> <a href="registrar-usuario.php">Nuevo Usuario</a> </p>

<?php if (isset($error_message)): ?>
  <p class="problema"><?php echo $errors['database']; ?></p>
<?php elseif (empty($usuarios)): ?>
	<p class="alert warning">No hay registros</p>
<?php else: ?>
	<table>
			<thead>
				<tr>
					<th>#</th>
					<th>Descripción</th>
					<th>Usuario</th>
					<th>Rol</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($usuarios as $usuario): ?>
				<tr>
					<td><?= htmlspecialchars($usuario['id']); ?></td>
					<td><?= htmlspecialchars($usuario['descripcion']); ?></td>
					<td><?= htmlspecialchars($usuario['username']); ?></td>
					<td><?= htmlspecialchars($usuario['rol']); ?></td>
					<td>
						<a href="actualizar-usuario.php?id=<?= $usuario['id']; ?>">Actualizar</a> |
						<a href="borrar-usuario.php?id=<?= $usuario['id']; ?>">Borrar</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
<?php endif; ?>

<hr>
<p>
	Bienvenido(a), <?= htmlspecialchars($_SESSION['descripcion']) ?> |
	<a href="logout.php">Cerrar sesión</a> 
</p>

<?php include_once 'templates/footer.php'; ?>