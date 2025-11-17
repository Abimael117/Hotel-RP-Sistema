<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");

// Obtener reservas activas
$sql = "SELECT 
            r.id,
            r.fecha_check_in,
            r.fecha_check_out,
            h.numero AS habitacion_numero,
            hu.nombre_completo AS huesped_nombre,
            r.metodo_pago,
            r.estado
        FROM reservas r
        JOIN habitaciones h ON r.habitacion_id = h.id
        JOIN huespedes hu ON r.huesped_id = hu.id
        WHERE r.estado = 'Activa'";

$reservas = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include("../views/layout/header.php"); 
?>

<h1>Calendario de Reservas</h1>
<p class="page-description">Visualiza todas las reservas por fecha.</p>

<link rel="stylesheet" href="../public/assets/css/calendario.css">

<div id="calendar"></div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<!-- Pasar reservas al JS -->
<script>
window.reservasCalendario = <?= json_encode($reservas); ?>;
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {

        initialView: 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        height: 'auto',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },

        // Mostrar eventos (bloques en el calendario)
        events: [
            <?php foreach ($reservas as $r): ?>
            {
                title: "<?= addslashes($r['huesped_nombre']); ?> (Hab. <?= $r['habitacion_numero']; ?>)",
                start: "<?= $r['fecha_check_in']; ?>",
                end: "<?= date('Y-m-d', strtotime($r['fecha_check_out'].' +1 day')); ?>",
                color: "#4F46E5",
                textColor: "#fff"
            },
            <?php endforeach; ?>
        ],

        // ⭐ MARCAR DÍAS CON RESERVAS
        dateDidMount(info) {
            const fecha = info.date.toISOString().split("T")[0];

            const lista = window.reservasCalendario.filter(r =>
                fecha >= r.fecha_check_in && fecha < r.fecha_check_out
            );

            if (lista.length > 0) {
                info.el.classList.add("dia-con-reservas");
                info.el.onclick = () => abrirModalDia(fecha, lista);
            }
        }
    });

    calendar.render();
});

// ==========================================
// MODAL
// ==========================================
function abrirModalDia(fecha, reservas) {

    document.getElementById("modalDiaTitulo").innerText =
        "Reservas del " + fecha;

    const cont = document.getElementById("listaReservasDia");
    cont.innerHTML = "";

    reservas.forEach(r => {
        cont.innerHTML += `
            <div class="reserva-item">
                <strong>Habitación ${r.habitacion_numero}</strong><br>
                Huésped: ${r.huesped_nombre}<br>
                Pago: ${r.metodo_pago}<br>
                Estado: ${r.estado}<br>
                <small>${r.fecha_check_in} → ${r.fecha_check_out}</small>
            </div>
        `;
    });

    document.getElementById("modalDia").style.display = "flex";
}

function cerrarModalDia() {
    document.getElementById("modalDia").style.display = "none";
}
</script>

<!-- MODAL -->
<div id="modalDia" class="modal-dia" style="display:none;">
    <div class="modal-content-dia">
        <h3 id="modalDiaTitulo">Reservas</h3>
        <div id="listaReservasDia"></div>

        <button onclick="cerrarModalDia()" class="btn-cerrar-dia">Cerrar</button>
    </div>
</div>

<?php include("../views/layout/footer.php"); ?>
