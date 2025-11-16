<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
include("../views/layout/header.php");
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1>Habitaciones</h1>
    <button class="btn-add" onclick="openModal()">Añadir Habitación</button>
</div>

<p class="page-description">Gestiona las habitaciones del hotel y mira su estado en tiempo real.</p>

<div class="room-grid">
<?php foreach ($habitaciones as $hab): ?>
    <div class="room-card">

        <!-- Encabezado con menú -->
        <div class="room-header">
            <h3>Habitación <?= $hab['numero'] ?></h3>

            <div class="menu-container">
                <button class="menu-button">⋯</button>

                <div class="menu-dropdown">
                    <p class="menu-title">Acciones</p>

                    <button onclick="editarHabitacion(<?= $hab['id'] ?>)">Editar</button>

                    <button onclick="cambiarEstado(<?= $hab['id'] ?>, 'disponible')">
                        Marcar como Disponible
                    </button>

                    <button onclick="cambiarEstado(<?= $hab['id'] ?>, 'limpieza')">
                        Marcar como En Limpieza
                    </button>

                    <button onclick="cambiarEstado(<?= $hab['id'] ?>, 'mantenimiento')">
                        Marcar para Mantenimiento
                    </button>

                    <button onclick="cambiarEstado(<?= $hab['id'] ?>, 'ocupado')">
                        Marcar como Ocupado
                    </button>

                    <button onclick="cambiarEstado(<?= $hab['id'] ?>, 'reservado')">
                        Marcar como Reservado
                    </button>

                    <button class="menu-delete" onclick="eliminarHabitacion(<?= $hab['id'] ?>)">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Estado visual -->
        <span class="badge <?= strtolower($hab['estado']); ?>">
            <?= ucfirst($hab['estado']); ?>
        </span>

        <!-- Info -->
        <p class="room-info">
            <img src="/Hotel-RP/public/assets/img/bed.svg" class="icon">
            <?= $hab['tipo_nombre']; ?>
        </p>

        <p class="room-info">
            <img src="/Hotel-RP/public/assets/img/user.svg" class="icon">
            <?= $hab['capacidad']; ?> Personas
        </p>

        <p class="room-price">
            $<?= number_format($hab['precio_noche'], 2); ?> / noche
        </p>

    </div>
<?php endforeach; ?>
</div>

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="modalAdd">
    <div class="modal">
        <span class="close-btn" onclick="closeModal()">✕</span>
        <h2>Añadir Habitación</h2>

        <form id="formAdd" method="POST">

            <label>Número</label>
            <input type="text" name="numero" required>

            <label>Tipo</label>
            <select name="tipo_id" required>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?php echo $t['id']; ?>">
                        <?php echo $t['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Capacidad (personas)</label>
            <input type="number" name="capacidad" min="1" required>

            <label>Precio por noche</label>
            <input type="number" name="precio_noche" step="0.01" required>

            <label>Estado</label>
            <select name="estado" required>
                <option value="disponible">Disponible</option>
                <option value="ocupado">Ocupado</option>
                <option value="reservado">Reservado</option>
                <option value="limpieza">Limpieza</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================
     MODAL OPEN/CLOSE
================================ -->
<script>
function openModal() {
    document.getElementById('modalAdd').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalAdd').style.display = 'none';
}
</script>

<!-- ================================
     AJAX: Guardar habitación
================================ -->
<script>
document.getElementById("formAdd").addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = new FormData(this);

    const btn = document.querySelector(".btn-save");
    btn.disabled = true;
    btn.innerText = "Guardando...";

    try {
        const res = await fetch("../controllers/add_habitacion.php", {
            method: "POST",
            body: form
        });

        const data = await res.json();

        if (data.success) {
            closeModal();
            showToast("Habitación añadida con éxito ✨");
            agregarHabitacionAlGrid(data.habitacion);
        } else {
            showToast("Error: " + data.message, true);
        }

    } catch (err) {
        showToast("Error de conexión", true);
    }

    btn.disabled = false;
    btn.innerText = "Guardar";
});
</script>

<!-- ================================
     AGREGAR HABITACIÓN AL GRID
================================ -->
<script>
function agregarHabitacionAlGrid(h) {
    const grid = document.querySelector(".room-grid");

    const card = document.createElement("div");
    card.classList.add("room-card");

    card.innerHTML = `
        <h2>Habitación ${h.numero}</h2>
        <span class="badge ${h.estado.toLowerCase()}">${h.estado}</span>

        <p class="room-info">
            <img src="/Hotel-RP/public/assets/img/bed.svg" class="icon">
            ${h.tipo_nombre}
        </p>

        <p class="room-info">
            <img src="/Hotel-RP/public/assets/img/user.svg" class="icon">
            ${h.capacidad} Personas
        </p>

        <p class="room-price">
            $${Number(h.precio_noche).toFixed(2)} / noche
        </p>
    `;

    grid.appendChild(card);
}
</script>

<!-- ================================
     TOAST
================================ -->
<script>
function showToast(msg, error = false) {
    const t = document.createElement("div");
    t.className = "toast" + (error ? " error" : "");
    t.innerText = msg;

    document.body.appendChild(t);

    setTimeout(() => t.classList.add("show"), 50);
    setTimeout(() => {
        t.classList.remove("show");
        setTimeout(() => t.remove(), 300);
    }, 3000);
}
</script>

<!-- ================================
     MENÚ DE TRES PUNTOS (...)
================================ -->
<script>
document.addEventListener("click", function(e) {
    const btn = e.target.closest(".menu-button");
    const allMenus = document.querySelectorAll(".menu-dropdown");

    if (!btn) {
        allMenus.forEach(m => m.classList.remove("show"));
        return;
    }

    const menu = btn.nextElementSibling;
    allMenus.forEach(m => {
        if (m !== menu) m.classList.remove("show");
    });

    menu.classList.toggle("show");
});
</script>

<!-- ================================
     FUNCIONES DE ACCIONES (NUEVO)
================================ -->
<script>

// =============================
// CAMBIAR ESTADO
// =============================
function cambiarEstado(id, nuevo) {
    window.location.href = `../controllers/habitaciones.php?accion=estado&id=${id}&valor=${nuevo}`;
}

// =============================
// ELIMINAR HABITACIÓN
// =============================
function eliminarHabitacion(id) {
    if (!confirm("¿Seguro que deseas eliminar esta habitación?")) return;

    window.location.href = `../controllers/habitaciones.php?accion=eliminar&id=${id}`;
}

// =============================
// EDITAR (se implementa después)
// =============================
function editarHabitacion(id) {
    alert("La edición la agregamos en el siguiente módulo 😎");
}

</script>


<?php include("../views/layout/footer.php"); ?>
