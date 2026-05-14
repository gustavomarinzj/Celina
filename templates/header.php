<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Celina</title>

	<link rel="stylesheet" href="css/bulma.min.css" type="text/css">

	<link rel="stylesheet" href="css/estilo.css" type="text/css">
	
</head>
<body>

<nav class="navbar has-background-link-05 mb-5">
  <div class="navbar-brand">
    <a href="home" class="navbar-item">Celina</a>
  </div>
</nav>

<div class="container">
  <div class="columns">
    <div class="column is-3">
      
      <aside class="menu">
        <p class="menu-label">General</p>
        <ul class="menu-list">
          <li><a href="home.php">Home</a></li>
          <?php if ($_SESSION['rol'] == 'administrador'): ?>
              <li>
                <a href="gestion-usuario.php">Gestión de Usuarios</a>
                <ul>
                  <li><a href="registrar-usuario.php">Nuevo Usuario</a></li>
                </ul>
              </li>
          <?php endif; ?>
        </ul>
      </aside>
    </div>

<div class="column is-9">
<!--   <nav class="breadcrumb" aria-label="breadcrumbs">
    <ul>
      <li><a href="#">Bulma</a></li>
      <li><a href="#">Documentation</a></li>
      <li><a href="#">Components</a></li>
      <li class="is-active"><a href="#" aria-current="page">Breadcrumb</a></li>
    </ul>
  </nav> -->
  <section class="hero is-small has-background-link-light mb-5">
    <div class="hero-body">
      <div class="container">
        <p class="has-text-link-dark title is-4"> Hola, <?= htmlspecialchars($_SESSION['descripcion']) ?>. </p>
        <p class="subtitle"> <a href="logout.php" class="has-text-link-dark is-5">Cerrar sesión</a> </p>
      </div>
    </div>
  </section>

  <div class="columns">
    <div class="column is-6">