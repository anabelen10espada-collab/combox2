<?php
ob_start();
require("./config/database.php");
ob_end_clean();

function resolverFotoLista($rutaBD) {
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

$sql = "
    SELECT
        id,
        firstname,
        lastname,
        mobile_phone,
        email,
        CASE
            WHEN users.status = true THEN 'Active'
            ELSE 'Inactive'
        END AS user_status,
        photo_url
    FROM
        public.users
    ORDER BY
        lastname ASC
";

$res = pg_query($supa_conn, $sql);

if (!$res) {
    die("Error en la consulta: " . pg_last_error($supa_conn));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Users</title>
    <style>
        body {
            margin: 0;
            padding: 35px 20px;
            font-family: Arial, sans-serif;
            background-color: #eef4fb;
            color: #1e3a8a;
        }
        h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #1d4ed8;
        }
        .tabla-users {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.08);
        }
        .tabla-users th {
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 14px;
            text-align: left;
            font-size: 14px;
            font-weight: bold;
        }
        .tabla-users td {
            padding: 10px 14px;
            border-bottom: 1px solid #dbeafe;
            font-size: 14px;
            color: #0f172a;
            vertical-align: middle;
        }
        .tabla-users tr:nth-child(even) td {
            background-color: #f8fbff;
        }
        .tabla-users tr:hover td {
            background-color: #eff6ff;
        }
        .tabla-users tr:last-child td {
            border-bottom: none;
        }
        .tabla-users td:last-child,
        .tabla-users th:last-child {
            text-align: center;
            white-space: nowrap;
        }
        .tabla-users td:nth-child(6),
        .tabla-users th:nth-child(6) {
            text-align: center;
        }
        .tabla-users a {
            display: inline-block;
            padding: 4px;
            border-radius: 4px;
            text-decoration: none;
        }
        .tabla-users a:hover {
            background-color: #dbeafe;
        }

        .modal-fondo {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }
        .modal-caja {
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 10px;
            max-width: 380px;
            width: 90%;
            text-align: center;
            font-family: Arial, sans-serif;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        .modal-botones {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 18px;
            flex-wrap: wrap;
        }
        .btn-inactivar {
            background-color: #d97706;
            color: #ffffff;
            border: none;
            padding: 9px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-eliminar {
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 9px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancelar {
            background-color: #e5e7eb;
            color: #111827;
            border: none;
            padding: 9px 14px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <center>
        <h3>List all users</h3>
    </center>

    <table class="tabla-users">
        <tr>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Mobile Phone</th>
            <th>Email</th>
            <th>User Status</th>
            <th>Photo</th>
            <th>...</th>
        </tr>

        <?php
        while ($row = pg_fetch_assoc($res)) {
            $idUsuario = (int)$row['id'];
            $nombreRaw = $row['firstname'] . ' ' . $row['lastname'];
            $nombreAttr = htmlspecialchars($nombreRaw, ENT_QUOTES, 'UTF-8');

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['firstname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lastname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mobile_phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['user_status']) . "</td>";

            // PHOTO
            $foto = resolverFotoLista($row['photo_url'] ?? '');

            echo "<td>";
            echo "<img src='" . htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') . "'
                  width='60'
                  height='60'
                  style='border-radius:50%; object-fit:cover;'
                  alt='Foto de perfil'
                  onerror=\"this.onerror=null;this.src='photos/usuario.png';\">";
            echo "</td>";

            // ACTIONS
            echo "<td>";

            // 1. SEARCH / VER (Lupa)
            echo "<a href='view_user.php?id=" . $idUsuario . "'>";
            echo "<img src='icons/lupa.png' width='20' height='20' alt='Search'>";
            echo "</a>&nbsp;";

            // 2. EDIT (Lápiz)
            echo "<a href='edit_user.php?id=" . $idUsuario . "'>";
            echo "<img src='icons/lapiz.png' width='20' height='20' alt='Edit'>";
            echo "</a>&nbsp;";

            // 3. DELETE / INACTIVATE (Eliminar)
            echo "<a href='#' data-id='" . $idUsuario . "' data-nombre=\"" . $nombreAttr . "\" onclick=\"abrirModalDesdeBoton(this); return false;\">";
            echo "<img src='icons/delete.png' width='20' height='20' alt='Delete'>";
            echo "</a>";

            echo "</td>";

            echo "</tr>";
        }
        ?>
    </table>

    <!-- MODAL DE CONFIRMACIÓN CON BOTONES -->
    <div id="modalAccion" class="modal-fondo">
        <div class="modal-caja">
            <h4>Confirmar acción</h4>
            <p id="textoModal">¿Qué deseas hacer con este usuario?</p>

            <form id="formAccionUsuario" action="delete_user.php" method="GET" class="modal-botones">
                <input type="hidden" name="id" id="inputIdUsuario" value="">
                <input type="hidden" name="action" id="inputActionUsuario" value="">

                <button type="submit" class="btn-inactivar" onclick="document.getElementById('inputActionUsuario').value = 'deactivate';">
                    Inactivar
                </button>

                <button type="submit" class="btn-eliminar" onclick="document.getElementById('inputActionUsuario').value = 'delete'; return confirm('¿Seguro que deseas ELIMINAR de forma permanente a ' + usuarioSeleccionadoNombre + '?');">
                    Eliminar
                </button>

                <button type="button" class="btn-cancelar" onclick="cerrarModal()">
                    Cancelar
                </button>
            </form>
        </div>
    </div>

    <script>
        let usuarioSeleccionadoNombre = "";

        function abrirModalDesdeBoton(elemento) {
            const id = elemento.getAttribute("data-id");
            usuarioSeleccionadoNombre = elemento.getAttribute("data-nombre");

            document.getElementById("inputIdUsuario").value = id;
            document.getElementById("inputActionUsuario").value = "";
            document.getElementById("textoModal").textContent =
                "¿Qué deseas hacer con el usuario " + usuarioSeleccionadoNombre + "?";
            document.getElementById("modalAccion").style.display = "flex";
        }

        function cerrarModal() {
            document.getElementById("modalAccion").style.display = "none";
        }
    </script>
</body>
</html>