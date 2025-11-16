<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
include("../views/layout/header.php");
?>


<?php if (isset($preciosGlobal)) : ?>
<script>
    window.preciosGlobal = <?= json_encode($preciosGlobal); ?>;
</script>
<?php endif; ?>

<h1>Reservas</h1>
<p class="page-description">Ver, crear y gestionar reservas.</p>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <h2>Gestión de Reservas</h2>
            <p style="font-size:14px; color:#6b7280;">Ver, crear y gestionar reservas.</p>
        </div>
        <button class="btn-add" onclick="openReservaModal()">Nueva Reserva</button>
    </div>

    <div class="search-box">
        <input type="text" id="buscadorReserva" placeholder="Buscar reservas por huésped...">
    </div>

    <table class="table-reservas">
        <thead>
            <tr>
                <th>Huésped</th>
                <th>Habitación</th>
                <th>Fechas</th>
                <th>Método Pago</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="tbodyReservas">
            <?php foreach ($reservas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['huesped_nombre']); ?></td>
                    <td><?= htmlspecialchars($r['habitacion_numero']); ?></td>
                    <td><?= $r['fecha_check_in']; ?> a <?= $r['fecha_check_out']; ?></td>
                    <td><?= htmlspecialchars($r['metodo_pago']); ?></td>
                    <td>
                        <span class="badge estado-<?= strtolower($r['estado']); ?>">
                            <?= $r['estado']; ?>
                        </span>
                    </td>
                    <td>$<?= number_format($r['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ===== MODAL NUEVA RESERVA ===== -->
<div class="modal-overlay" id="modalReserva">
    <div class="modal">
        <span class="close-btn" onclick="closeReservaModal()">✕</span>
        <h2>Nueva Reserva</h2>

        <form id="formReserva" method="POST">

            <!-- Huesped -->
            <label>Huésped</label>
           <!-- Boton de Nuevo Huesped -->
            <button type="button" class="btn-mini" onclick="openNuevoHuesped()">
                ➕ Nuevo huésped
            </button>
            <!---->
            <select name="huesped_id" required>
                <?php foreach ($huespedes as $h): ?>
                    <option value="<?= $h['id']; ?>">
                        <?= htmlspecialchars($h['nombre_completo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Habitación -->
            <label>Habitación</label>
            <select name="habitacion_id" id="habitacionSelect" required>
                <?php foreach ($habitaciones as $h): ?>
                    <option 
                        value="<?= $h['id']; ?>"
                        data-tipo="<?= $h['tipo_id']; ?>"
                        data-precio-base="<?= $h['precio_noche']; ?>"
                        data-numero="<?= $h['numero']; ?>"
                    >
                        Hab. <?= $h['numero']; ?> (<?= $h['tipo_nombre']; ?>) – Cap: <?= $h['capacidad']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Ocupación -->
            <label>Ocupación</label>

            <div class="ocupacion-row">
                <label class="radio-option">
                    <input type="radio" name="ocupacion" value="sencilla">
                    <span>Sencilla</span>
                </label>

                <label class="radio-option">
                    <input type="radio" name="ocupacion" value="doble">
                    <span>Doble</span>
                </label>
            </div>


            <!-- Fechas -->
            <label>Fecha check-in</label>
            <input type="date" name="fecha_check_in" id="fechaIn" required>

            <label>Fecha check-out</label>
            <input type="date" name="fecha_check_out" id="fechaOut" required>

            <!-- Personas extra -->
            <label>Personas Extra</label>
            <input type="number" name="personas_extra" id="personasExtra" min="0" value="0">

            <!-- Método de pago -->
            <label>Método de pago</label>
            <select name="metodo_pago" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
            </select>

            <!-- Preview básico -->
            <p id="totalPreview" style="font-weight:600; margin-top:4px; margin-bottom:10px;">
                Total estimado: $0.00
            </p>

            <!-- DESGLOSE DETALLADO -->
            <div id="desglose-box" class="desglose-box">
                <h4>Desglose de Pago</h4>

                <div class="desglose-item">
                    <span>Tipo de Habitación:</span>
                    <span id="dg_tipo">—</span>
                </div>

                <div class="desglose-item">
                    <span>Precio base por noche:</span>
                    <span id="dg_precio">$0.00</span>
                </div>

                <div class="desglose-item">
                    <span>Costo personas extra:</span>
                    <span id="dg_extra">$0.00</span>
                </div>

                <div class="desglose-item">
                    <span>Número de noches:</span>
                    <span id="dg_noches">0</span>
                </div>

                <div class="desglose-item total">
                    <span>Total a pagar:</span>
                    <span id="dg_total">$0.00</span>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeReservaModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>
<!-- ======================================
     MINI MODAL: NUEVO HUÉSPED
====================================== -->
<div class="modal-overlay" id="modalNuevoHuesped" style="display:none;">
    <div class="modal mini">
        <span class="close-btn" onclick="closeNuevoHuesped()">✕</span>
        <h3>Nuevo Huésped</h3>

        <form id="formNuevoHuesped">
            <label>Nombre completo</label>
            <input type="text" name="nombre_completo" required>

            <label>Teléfono</label>
            <input type="text" name="telefono">

            <label>Email</label>
            <input type="email" name="email">

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeNuevoHuesped()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>


<!-- JS abrir/cerrar modal -->
<script>
function openReservaModal() {
    document.getElementById('modalReserva').style.display = 'flex';
}
function closeReservaModal() {
    document.getElementById('modalReserva').style.display = 'none';
}
</script>

<!-- AJAX: Guardar reserva -->
<script>
document.getElementById("formReserva").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = new FormData(this);
    const btn = document.querySelector("#formReserva .btn-save");
    btn.disabled = true;
    btn.textContent = "Guardando...";

    try {
        const res = await fetch("../controllers/add_reserva.php", {
            method: "POST",
            body: form
        });
        const data = await res.json();

        if (data.success) {
            closeReservaModal();
            showToast("Reserva registrada ✨");
            agregarReservaATabla(data.reserva);
        } else {
            showToast(data.message || "Error al guardar", true);
        }
    } catch (err) {
        showToast("Error de conexión", true);
    }

    btn.disabled = false;
    btn.textContent = "Guardar";
});
</script>

<!-- Insertar nueva reserva en la tabla -->
<script>
function agregarReservaATabla(r) {
    const tbody = document.getElementById("tbodyReservas");

    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td>${r.huesped_nombre}</td>
        <td>${r.habitacion_numero}</td>
        <td>${r.fecha_check_in} a ${r.fecha_check_out}</td>
        <td>${r.metodo_pago}</td>
        <td><span class="badge estado-${r.estado.toLowerCase()}">${r.estado}</span></td>
        <td>$${Number(r.total).toFixed(2)}</td>
    `;
    tbody.prepend(tr);
}
</script>

<!-- CALCULO EN VIVO DEL DESGLOSE -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    const habSelect    = document.querySelector("#habitacionSelect");
    const ocupacionSel = document.querySelectorAll("input[name='ocupacion']");
    const extraInput   = document.querySelector("#personasExtra");
    const checkIn      = document.querySelector("#fechaIn");
    const checkOut     = document.querySelector("#fechaOut");

    const dg_tipo   = document.querySelector("#dg_tipo");
    const dg_precio = document.querySelector("#dg_precio");
    const dg_extra  = document.querySelector("#dg_extra");
    const dg_noches = document.querySelector("#dg_noches");
    const dg_total  = document.querySelector("#dg_total");
    const totalPrev = document.querySelector("#totalPreview");

    function calcular() {

        if (!habSelect.value) return;

        const selected = habSelect.selectedOptions[0];
        const tipoId   = selected.dataset.tipo;
        const ocup     = document.querySelector("input[name='ocupacion']:checked").value;
        const extra    = parseInt(extraInput.value) || 0;

        let precioBase = 0;

        if (window.preciosGlobal 
            && window.preciosGlobal[tipoId] 
            && window.preciosGlobal[tipoId][ocup]) {

            precioBase = parseFloat(window.preciosGlobal[tipoId][ocup]);
        } else {
            precioBase = parseFloat(selected.dataset.precioBase || 0);
        }

        dg_tipo.textContent = (ocup === "sencilla") ? "Sencilla" : "Doble";

        const f1 = new Date(checkIn.value);
        const f2 = new Date(checkOut.value);

        let noches = 0;
        if (!isNaN(f1) && !isNaN(f2) && f2 > f1) {
            noches = (f2 - f1) / (1000 * 60 * 60 * 24);
        }
        dg_noches.textContent = noches;

        const costoExtra = extra * 100 * noches;
        dg_extra.textContent = `$${costoExtra.toFixed(2)}`;

        const subtotal = (precioBase * noches) + costoExtra;

        dg_precio.textContent = `$${precioBase.toFixed(2)}`;
        dg_total.textContent  = `$${subtotal.toFixed(2)}`;
        totalPrev.textContent = `Total estimado: $${subtotal.toFixed(2)}`;
    }

    habSelect.addEventListener("change", calcular);
    ocupacionSel.forEach(r => r.addEventListener("change", calcular));
    extraInput.addEventListener("input", calcular);
    checkIn.addEventListener("change", calcular);
    checkOut.addEventListener("change", calcular);

    calcular();
});
</script>

<script>
function openNuevoHuesped() {
    document.getElementById("modalNuevoHuesped").style.display = "flex";
}
function closeNuevoHuesped() {
    document.getElementById("modalNuevoHuesped").style.display = "none";
}
</script>
<script>
document.getElementById("formNuevoHuesped").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = new FormData(this);

    try {
        const res = await fetch("../controllers/add_huesped.php", {
            method: "POST",
            body: form
        });

        const data = await res.json();

        if (data.success) {
            // 1. Cerrar modal
            closeNuevoHuesped();

            // 2. Insertar en el SELECT
            const select = document.getElementById("huespedSelect");
            const opt = document.createElement("option");
            opt.value = data.huesped.id;
            opt.text = data.huesped.nombre_completo;
            select.appendChild(opt);

            // 3. Seleccionarlo
            select.value = data.huesped.id;

            showToast("Huésped agregado ✨");

        } else {
            showToast(data.message || "Error al guardar", true);
        }

    } catch (err) {
        showToast("Error de conexión", true);
    }
});
</script>

<?php include("../views/layout/footer.php"); ?>
