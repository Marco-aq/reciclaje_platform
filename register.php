<?php
$host = 'localhost';
$dbname = 'reciclaje_platform';
$username = 'root'; 
$password = 'Miperritoeszeuz1'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Todos los campos son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("El correo electrónico no es válido.");
    }

    if ($password !== $confirm_password) {
        die("Las contraseñas no coinciden.");
    }

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar en la base de datos
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash) VALUES (:nombre, :apellido, :email, :password_hash)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);

        // Mostrar mensaje y redirigir
        echo "<div style='text-align: center; margin-top: 20px; font-family: Poppins, sans-serif;'>
                <h2 style='color: #10B981;'>Registro exitoso</h2>
                <p>Bienvenido a la plataforma</p>
              </div>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'home-page.php';
                }, 1500);
              </script>";
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código de error para duplicados
            die("El correo electrónico ya está registrado.");
        }
        die("Error al registrar el usuario: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #D1FAE5; /* Color de fondo */
      /* Elimina flex y height: 100vh para que el header quede arriba */
    }

    .row {
      display: flex;
      width: 90%;
      max-width: 1200px; /* Asegura que no se desborde */
      gap: 40px; /* Espacio visible entre los contenedores */
      margin: 0 auto;
      margin-top: 100px; /* Deja espacio para el header */
    }

    .left, .right {
      flex: 1;
      min-height: 500px; /* Asegura que ambos contenedores tengan la misma altura */
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-radius: 12px; /* Bordes redondeados */
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Sombra ligera */
    }

    .left {
      background-color: white;
      align-items: center;
      padding: 30px;
      text-align: center;
    }

    .right {
      background-image: url('https://img.freepik.com/vector-premium/comunidad-organiza-evento-plantacion-arboles-compensar-emisiones-carbono-mejorar-vector-calidad-aire_216520-163843.jpg');
      background-size: cover;
      background-position: center;
      align-items: center;
      justify-content: center;
      display: flex;
      border-radius: 12px; /* Bordes redondeados */
    }

    .logo {
      width: 60px; /* Ancho fijo */
      height: 40px; /* Alto fijo */
      margin-bottom: 20px; /* Espacio debajo de la imagen */
    }

    input[type="text"] {
      padding: 10px; /* Sin padding adicional porque no hay íconos */
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 100%; /* Asegura que no exceda el ancho del contenedor */
      box-sizing: border-box; /* Incluye el padding y el borde en el ancho total */
      opacity: 0.7;
    }

    input[type="email"], input[type="password"] {
      padding: 10px 10px 10px 40px; /* Asegura espacio suficiente para los íconos */
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 100%; /* Asegura que no exceda el ancho del contenedor */
      box-sizing: border-box; /* Incluye el padding y el borde en el ancho total */
      opacity: 0.7;
    }

    .input-icon {
      position: absolute;
      top: 50%;
      left: 10px;
      transform: translateY(-50%);
      color: black; /* Íconos no transparentes */
      font-size: 16px; /* Tamaño del ícono */
    }

    @media (max-width: 768px) {
      .row {
        flex-direction: column;
        gap: 20px;
      }

      .right {
        height: 200px; /* Ajusta la altura para pantallas pequeñas */
      }
    }
  </style>
</head>
<body>
  <!-- HEADER INCLUIDO DESDE components/header.php -->
  <?php include 'components/header.php'; ?>

  <div class="row">
    <div class="left">
      <img class="logo" src="https://e7.pngegg.com/pngimages/216/100/png-clipart-recycling-symbol-logo-recycling-bin-recycle-miscellaneous-angle.png" alt="Logo de reciclaje">
      <div class="title" style="font-weight: 700; font-family: 'Poppins', sans-serif; font-size: 24px;">Únete a la comunidad</div>
      <div class="subtitle">Crea tu cuenta de reciclaje</div>
      
      <!-- Formulario -->
      <form method="POST" style="display: flex; flex-direction: column; align-items: flex-start; gap: 20px; margin-top: 20px; width: 80%;">
        <!-- Nombre y Apellido -->
        <div style="display: flex; justify-content: space-between; gap: 10px; width: 100%;">
          <div style="flex: 1; display: flex; flex-direction: column;">
            <label for="nombre" style="font-weight: 400; font-size: 16px; text-align: left;">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="juan" />
          </div>
          <div style="flex: 1; display: flex; flex-direction: column;">
            <label for="apellido" style="font-weight: 400; font-size: 16px; text-align: left;">Apellido</label>
            <input type="text" id="apellido" name="apellido" placeholder="perez" />
          </div>
        </div>

        <!-- Correo Electrónico -->
        <div style="width: 100%; display: flex; flex-direction: column;">
          <label for="email" style="font-weight: 400; font-size: 16px; text-align: left;">Correo Electrónico</label>
          <div style="position: relative; width: 100%;">
            <span class="input-icon">&#9993;</span> <!-- Ícono de carta -->
            <input type="email" id="email" name="email" placeholder="tu@email.com" />
          </div>
        </div>

        <!-- Contraseña -->
        <div style="width: 100%; display: flex; flex-direction: column;">
          <label for="password" style="font-weight: 400; font-size: 16px; text-align: left;">Contraseña</label>
          <div style="position: relative; width: 100%;">
            <span class="input-icon">&#128274;</span> <!-- Ícono de candado -->
            <input type="password" id="password" name="password" placeholder="***********" />
          </div>
        </div>

        <!-- Confirmar Contraseña -->
        <div style="width: 100%; display: flex; flex-direction: column;">
          <label for="confirm-password" style="font-weight: 400; font-size: 16px; text-align: left;">Confirmar Contraseña</label>
          <div style="position: relative; width: 100%;">
            <span class="input-icon">&#128274;</span> <!-- Ícono de candado -->
            <input type="password" id="confirm-password" name="confirm-password" placeholder="***********" />
          </div>
        </div>

        <!-- Acepto términos -->
        <div style="width: 100%; display: flex; align-items: center; gap: 10px;">
          <input type="checkbox" id="terms" name="terms" style="width: 16px; height: 16px;" />
          <label for="terms" style="font-weight: 400; font-size: 14px;">Acepto los términos y condiciones y la política de privacidad</label>
        </div>

        <!-- Botón Crear Cuenta -->
        <button id="submit-btn" type="submit" style="width: 100%; padding: 10px; background-color: #10B981; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;" disabled>
          Crear cuenta
        </button>

        <!-- Enlace Inicia Sesión -->
        <div style="width: 100%; text-align: center; margin-top: 10px;">
          <span style="font-size: 14px;">¿Ya tienes una cuenta? </span>
          <a href="login.php" style="color: #10B981; text-decoration: none; font-weight: 500;">Inicia Sesión</a>
        </div>
      </form>

      <script>
        // Obtener el checkbox y el botón
        const termsCheckbox = document.getElementById('terms');
        const submitButton = document.getElementById('submit-btn');

        // Escuchar cambios en el checkbox
        termsCheckbox.addEventListener('change', function () {
          // Habilitar o deshabilitar el botón según el estado del checkbox
          submitButton.disabled = !this.checked;
        });
      </script>
    </div>

    <div class="right"></div>
  </div>
</body>
</html>
