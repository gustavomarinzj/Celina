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
  $query = "SELECT id, descripcion, username, rol FROM usuario ORDER BY id ASC";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $usuarios = $stmt->fetchAll();  

} catch (PDOException $e) {
	$error_message = "Error: " . $e->getMessage();
}

?>
<h4 class="title is-4">Gestión de Usuarios</h4>

<?php if (isset($error_message)): ?>
  <p class="has-text-danger"><?php echo $errors['database']; ?></p>
<?php elseif (empty($usuarios)): ?>
	<div class="notification is-info">No hay registros</div>
<?php else: ?>
<div class="table-container">
	<table class="table">
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
						<a href="actualizar-usuario.php?id=<?= $usuario['id']; ?>" class="button is-success"><button>Actualizar</button></a>
						<a href="borrar-usuario.php?id=<?= $usuario['id']; ?>" class="button is-danger"><button>Borrar</button></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endif; ?>

<?php include_once 'templates/footer.php'; ?>