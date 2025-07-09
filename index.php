<?php
$host = "localhost";
$user = "root";
$pass = "";
$database = "database_usuarios";
$conn = new mysqli($host, $user, $pass, $database);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    foreach ($data as $row) {
        $id = (int)$row['id_cit'];
        $cit = $conn->real_escape_string($row['cit_cit']);
        $hor = $conn->real_escape_string($row['hor_cit']);
        $nom = $conn->real_escape_string($row['nom_cit']);
        $tel = $conn->real_escape_string($row['tel_cit']);
        $sql = "UPDATE cita SET cit_cit='$cit', hor_cit='$hor', nom_cit='$nom', tel_cit='$tel' WHERE id_cit=$id";
        $conn->query($sql);
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// SELECT
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fecha'])) {
    header('Content-Type: application/json');
    $fecha = $_GET['fecha'];
    $res = $conn->query("SELECT id_cit, cit_cit, hor_cit, nom_cit, tel_cit FROM cita WHERE cit_cit = '$fecha'");
    $data = array();
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// INSERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['insert'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    $cit = $conn->real_escape_string($data['cit_cit']);
    $hor = $conn->real_escape_string($data['hor_cit']);
    $nom = $conn->real_escape_string($data['nom_cit']);
    $tel = $conn->real_escape_string($data['tel_cit']);
    $sql = "INSERT INTO cita (cit_cit, hor_cit, nom_cit, tel_cit) VALUES ('$cit', '$hor', '$nom', '$tel')";
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['delete'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)$data['id_cit'];
    $sql = "DELETE FROM cita WHERE id_cit = $id";
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Citas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/handsontable@8.4.0/dist/handsontable.full.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h3>📋 Gestión de Citas con Handsontable</h3>
    <hr>

    <!-- Formulario Agregar -->
    <div class="form-inline mb-3">
        <input type="text" id="nom_cit" class="form-control mr-2" placeholder="Nombre" required>
        <input type="date" id="cit_cit" class="form-control mr-2" value="<?php echo date('Y-m-d'); ?>" required>
        <input type="time" id="hor_cit" class="form-control mr-2" required>
        <input type="text" id="tel_cit" class="form-control mr-2" placeholder="Teléfono" required>
        <button class="btn btn-success" onclick="agregarCita()">➕ Agregar</button>
    </div>

    <!-- Formulario Eliminar -->
    <div class="form-inline mb-3">
        <input type="number" id="id_cit_delete" class="form-control mr-2" placeholder="ID a eliminar" required>
        <button class="btn btn-danger" onclick="eliminarCita()">❌ Eliminar</button>
    </div>

    <!-- Filtro Fecha -->
    <div class="form-inline mb-3">
        <label class="mr-2">📅 Fecha:</label>
        <input type="date" id="fechaFiltro" class="form-control mr-2" value="<?php echo date('Y-m-d'); ?>">
        <button class="btn btn-primary" onclick="cargarCitas()">🔍 Buscar</button>
        <button class="btn btn-info ml-2" onclick="guardarCambios()">💾 Guardar cambios</button>
    </div>

    <hr>
    <div id="hot"></div>

    <script src="https://cdn.jsdelivr.net/npm/handsontable@8.4.0/dist/handsontable.full.min.js"></script>
    <script>
        var hot;

        function cargarCitas() {
            const fecha = document.getElementById('fechaFiltro').value;
            fetch('index.php?fecha=' + fecha)
                .then(res => res.json())
                .then(data => {
                    if (hot) hot.destroy();

                    const now = new Date();
                    const vencidas = [];

                    // Detectar citas vencidas
                    data.forEach((row, i) => {
                        if (row.cit_cit && row.hor_cit) {
                            const citaDateTime = new Date(row.cit_cit + 'T' + row.hor_cit);
                            if (citaDateTime < now) {
                                vencidas.push(i);
                            }
                        }
                    });

                    hot = new Handsontable(document.getElementById('hot'), {
                        data: data,
                        colHeaders: ['ID', 'Fecha', 'Hora', 'Nombre', 'Teléfono'],
                        columns: [
                            { data: 'id_cit', readOnly: true },
                            { data: 'cit_cit', type: 'date', dateFormat: 'YYYY-MM-DD' },
                            { data: 'hor_cit', type: 'time', timeFormat: 'HH:mm' },
                            { data: 'nom_cit' },
                            { data: 'tel_cit' }
                        ],
                        cells: function (row, col) {
                            const props = {};
                            if (vencidas.includes(row)) {
                                props.className = 'table-danger';
                            }
                            return props;
                        },
                        licenseKey: 'non-commercial-and-evaluation',
                        stretchH: 'all',
                        width: '100%',
                        height: 400,
                        rowHeaders: true,
                        filters: true,
                        dropdownMenu: true
                    });
                });
        }

        function guardarCambios() {
            const rows = hot.getData().map((row, i) => {
                const columns = hot.getSettings().columns;
                const obj = {};
                for (let j = 0; j < columns.length; j++) {
                    obj[columns[j].data] = row[j];
                }
                return obj;
            });

            fetch('index.php?update=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(rows)
            })
            .then(res => res.json())
            .then(() => alert("✅ Cambios guardados"))
            .catch(() => alert("❌ Error al guardar"));
        }

        function agregarCita() {
            const nom = document.getElementById('nom_cit').value;
            const fecha = document.getElementById('cit_cit').value;
            const hora = document.getElementById('hor_cit').value;
            const tel = document.getElementById('tel_cit').value;

            if (!nom || !fecha || !hora || !tel) return alert("❗Todos los campos son obligatorios.");

            fetch('index.php?insert=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom_cit: nom, cit_cit: fecha, hor_cit: hora, tel_cit: tel })
            })
            .then(res => res.json())
            .then(r => {
                if (r.status === 'success') {
                    alert("✅ Cita agregada");
                    cargarCitas();
                } else {
                    alert("❌ Error: " + r.message);
                }
            });
        }

        function eliminarCita() {
            const id = document.getElementById('id_cit_delete').value;
            if (!id) return alert("❗Ingresa un ID válido.");

            fetch('index.php?delete=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_cit: id })
            })
            .then(res => res.json())
            .then(r => {
                if (r.status === 'success') {
                    alert("🗑️ Cita eliminada");
                    cargarCitas();
                } else {
                    alert("❌ Error: " + r.message);
                }
            });
        }

        cargarCitas(); // Auto carga al inicio
    </script>
</body>
</html>
