<?php include("../views/layout/header.php"); ?>

<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="../public/assets/css/movimientos.css">

<div class="container">

    <h1 class="titulo-modulo">Nueva Estancia</h1>
    <p class="descripcion-modulo">Registrar una estancia inmediata.</p>

    <?php if (!empty($errores)): ?>
        <div class="alerta-error">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="crear_estancia.php" class="formulario-mov">

        <div class="form-grid">

            <!-- HABITACIÓN -->
            <div class="form-group">
                <label>Habitación</label>
                <select name="habitacion_id" id="habitacion" required>
                    <option value="">Seleccionar habitación…</option>

                    <?php foreach ($habitaciones as $hab): ?>
                        <option 
                            value="<?= $hab['id'] ?>" 
                            data-tipo="<?= $hab['tipo_id'] ?>"
                        >
                            <?= "Habitación " . $hab['numero'] . " (" . $hab['estado'] . ")" ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- OCUPACIÓN DINÁMICA -->
            <div class="form-group" id="div-ocupacion" style="display:none;">
                <label>Tipo de ocupación</label>
                <select id="ocupacion"></select>
            </div>

            <!-- HUESPED -->
            <div class="form-group">
                <label>Huésped</label>
                <select name="huesped_id" required>
                    <option value="">Seleccionar huésped…</option>

                    <?php foreach ($huespedes as $hu): ?>
                        <option value="<?= $hu['id'] ?>">
                            <?= $hu['nombre_completo'] ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- CHECK-IN -->
            <div class="form-group">
                <label>Check-in</label>
                <input
                    type="date"
                    name="fecha_check_in"
                    value="<?= date('Y-m-d') ?>"
                    readonly
                >
            </div>

            <!-- CHECK-OUT -->
            <div class="form-group">
                <label>Check-out</label>
                <input
                    type="date"
                    name="fecha_check_out"
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                    required
                >
            </div>

            <!-- PERSONAS EXTRA -->
            <div class="form-group">
                <label>Personas extra</label>
                <input type="number" name="personas_extra" min="0" value="0">
            </div>

            <!-- TOTAL -->
            <div class="form-group">
                <label>Total ($)</label>
                <input type="number" step="0.01" name="total" id="total" min="0" value="0" required>
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="form-group">
                <label>Método de pago</label>
                <select name="metodo_pago" required>
                    <option value="">Selecciona método…</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>

            <!-- DESCUENTO -->
            <div class="form-group">
                <label>Descuento aplicado (%)</label>
                <input type="number" step="0.01" name="descuento_aplicado" min="0" value="0">
            </div>

        </div>

        <div class="acciones-form">
            <a href="movimientos.php" class="btn-secundario">Cancelar</a>
            <button type="submit" class="btn-primario">Guardar Estancia</button>
        </div>

    </form>
</div>

<?php include("../views/layout/footer.php"); ?>

<!-- ============================================
     SCRIPT: PRECIOS, OCUPACIONES Y DESCUENTO
============================================ -->
<script>
const precios = <?= json_encode($precios); ?>;

const habitacionSel = document.getElementById("habitacion");
const ocupacionSel  = document.getElementById("ocupacion");
const personasExtra = document.getElementsByName("personas_extra")[0];
const descuentoInput = document.getElementsByName("descuento_aplicado")[0];
const totalInput    = document.getElementById("total");
const divOcupacion  = document.getElementById("div-ocupacion");

let precioBaseGlobal = 0;

// =========================================
// ACTUALIZAR PRECIO BASE
// =========================================
function actualizarPrecio() {
    const habitacion = habitacionSel.options[habitacionSel.selectedIndex];
    if (!habitacion) return;

    const tipoId = habitacion.getAttribute("data-tipo");
    const preciosTipo = precios.filter(p => p.tipo_id == tipoId);

    if (preciosTipo.length === 0) return;

    let precioBase = 0;

    // SOLO UNA TARIFA
    if (preciosTipo.length === 1) {
        precioBase = parseFloat(preciosTipo[0].precio);
        divOcupacion.style.display = "none";

    } else {
        // VARIAS TARIFAS → MOSTRAR SELECT
        divOcupacion.style.display = "block";
        ocupacionSel.innerHTML = "";

        preciosTipo.forEach(p => {
            const opt = document.createElement("option");
            opt.value = p.precio;
            opt.dataset.precio = p.precio;
            opt.textContent = `${p.ocupacion} ($${p.precio})`;
            ocupacionSel.appendChild(opt);
        });

        precioBase = parseFloat(ocupacionSel.options[0].dataset.precio);
    }

    // PERSONAS EXTRA
    let total = precioBase;
    const extra = parseInt(personasExtra.value) || 0;
    total += extra * 100;

    precioBaseGlobal = total;   // Guardamos precio antes de descuento

    aplicarDescuento();
}

// =========================================
// APLICAR DESCUENTO (%)
// =========================================
function aplicarDescuento() {
    const desc = parseFloat(descuentoInput.value) || 0;

    if (desc <= 0) {
        totalInput.value = precioBaseGlobal.toFixed(2);
        return;
    }

    const totalFinal = precioBaseGlobal - (precioBaseGlobal * (desc / 100));
    totalInput.value = totalFinal.toFixed(2);
}

// =========================================
// EVENTOS
// =========================================
habitacionSel.addEventListener("change", actualizarPrecio);
ocupacionSel.addEventListener("change", actualizarPrecio);
personasExtra.addEventListener("input", actualizarPrecio);
descuentoInput.addEventListener("input", aplicarDescuento);

// Calcular al cargar
actualizarPrecio();
</script>
