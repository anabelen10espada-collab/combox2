<?php
require("./config/database.php");

$conexion = isset($supa_conn) ? $supa_conn : $local_conn;

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    die("ID de usuario no válido.");
}

$sql = 'SELECT * FROM users WHERE id = $1';
$result = pg_query_params($conexion, $sql, array($id));

if (!$result || pg_num_rows($result) == 0) {
    die("Usuario no encontrado.");
}

$user = pg_fetch_assoc($result);

function resolverFotoCombox2($rutaBD) {
    $default = 'photos/usuario.png';
    $ruta = trim((string)$rutaBD);

    if ($ruta === '') {
        return $default;
    }

    if (preg_match('#^https?://#i', $ruta)) {
        return $ruta;
    }

    $archivo = basename($ruta);
    if ($archivo === '') {
        return $default;
    }

    // 1. Buscar en combox2/photos/
    if (file_exists(__DIR__ . '/photos/' . $archivo)) {
        return 'photos/' . $archivo;
    }

    // 2. Buscar en combox/src/photos/
    if (file_exists(__DIR__ . '/../combox/src/photos/' . $archivo)) {
        return '../combox/src/photos/' . $archivo;
    }

    return $default;
}

$foto_actual = resolverFotoCombox2($user['photo_url'] ?? '');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Combox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-person-gear me-2"></i> Gestión de Usuarios - Combox</span>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                    <div class="d-flex align-items-center mb-4">
                        <a href="list_users.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
                        <h3 class="text-secondary fw-bold m-0">Editar Usuario</h3>
                    </div>

                    <form action="update_user.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre</label>
                                <input type="text" class="form-control" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Apellido</label>
                                <input type="text" class="form-control" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Celular</label>
                            <input type="text" class="form-control" name="mobile_phone" value="<?php echo htmlspecialchars($user['mobile_phone']); ?>" required>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="mb-4 text-center">
                            <label class="form-label fw-semibold d-block text-start mb-2">Fotografía de perfil</label>
                            <div class="mb-3">
                                <img id="previewFoto"
                                     src="<?php echo htmlspecialchars($foto_actual, ENT_QUOTES, 'UTF-8'); ?>"
                                     width="90"
                                     height="90"
                                     class="rounded-circle shadow-sm border"
                                     style="object-fit:cover;"
                                     alt="Foto actual"
                                     onerror="this.onerror=null;this.src='photos/usuario.png';">
                            </div>
                            <input type="file" id="inputFoto" class="form-control" name="photo" accept=".jpg,.jpeg,.png">
                            <div class="form-text text-start">Formatos permitidos: JPG o PNG.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="list_users.php" class="btn btn-light border px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Guardar Cambios</button>
                        </div>
                             
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('inputFoto').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('previewFoto').src = URL.createObjectURL(file);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>