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


<!-- ==========================================
     MODAL: NUEVA RESERVA
=========================================== -->
<div class="modal-overlay" id="modalReserva">
    <div class="modal">
        <span class="close-btn" onclick="closeReservaModal()">✕</span>
        <h2>Nueva Reserva</h2>

        <form id="formReserva" method="POST">

            <!-- Huésped -->
            <div class="form-group">
                <label>Huésped</label>
                <div style="width:65%;">
                    <button type="button" class="btn-mini" onclick="openNuevoHuesped()" style="margin-bottom:6px;">
                        ➕ Nuevo huésped
                    </button>
                    <select name="huesped_id" id="huespedSelect" required>
                        <?php foreach ($huespedes as $h): ?>
                            <option value="<?= $h['id']; ?>">
                                <?= htmlspecialchars($h['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Habitación -->
            <div class="form-group">
                <label>Habitación</label>
                <select name="habitacion_id" id="habitacionSelect" required>
                    <option value="">Selecciona fechas primero</option>
                </select>
            </div>

            <!-- Ocupación -->
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="ocupacion" value="sencilla" checked>
                    <span>Sencilla</span>
                </label>

                <label class="radio-option">
                    <input type="radio" name="ocupacion" value="doble">
                    <span>Doble</span>
                </label>
            </div>



            <!-- Fechas -->
            <div class="form-group">
                <label>Fecha check-in</label>
                <input type="date" name="fecha_check_in" id="fechaIn" required>
            </div>

            <div class="form-group">
                <label>Fecha check-out</label>
                <input type="date" name="fecha_check_out" id="fechaOut" required>
            </div>

            <!-- Personas extra -->
            <div class="form-group">
                <label>Personas Extra</label>
                <input type="number" name="personas_extra" id="personasExtra" min="0" value="0">
            </div>

            <!-- Método de pago -->
            <div class="form-group">
                <label>Método de pago</label>
                <select name="metodo_pago" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>

            <!-- Descuento -->
            <div class="form-group">
                <label>Descuento 10%</label>
                <div class="checkbox-row">
                    <input type="checkbox" id="descuento10" name="descuento10" value="1">
                    <span>Aplicar descuento</span>
                </div>
            </div>

            <!-- Preview -->
            <p id="totalPreview" style="font-weight:600; margin-top:4px; margin-bottom:10px;">
                Total estimado: $0.00
            </p>

            <!-- DESGLOSE -->
            <div id="desglose-box" class="desglose-box">
                <h4>Desglose de Pago</h4>

                <div class="desglose-item"><span>Tipo de Habitación:</span><span id="dg_tipo">—</span></div>
                <div class="desglose-item"><span>Precio base por noche:</span><span id="dg_precio">$0.00</span></div>
                <div class="desglose-item"><span>Costo personas extra:</span><span id="dg_extra">$0.00</span></div>
                <div class="desglose-item"><span>Número de noches:</span><span id="dg_noches">0</span></div>
                <div class="desglose-item"><span>Descuento:</span><span id="dg_descuento">—</span></div>

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


<!-- MINI MODAL: NUEVO HUÉSPED -->
<div class="modal-overlay" id="modalNuevoHuesped" style="display:none;">
    <div class="modal mini">
        <span class="close-btn" onclick="closeNuevoHuesped()">✕</span>
        <h3>Nuevo Huésped</h3>

        <form id="formNuevoHuesped">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre_completo" required>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeNuevoHuesped()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>


<!-- ABRIR/CERRAR -->
<script>
function openReservaModal(){ document.getElementById('modalReserva').style.display='flex'; }
function closeReservaModal(){ document.getElementById('modalReserva').style.display='none'; }

function openNuevoHuesped(){ document.getElementById("modalNuevoHuesped").style.display="flex"; }
function closeNuevoHuesped(){ document.getElementById("modalNuevoHuesped").style.display="none"; }
</script>


<!-- GUARDAR RESERVA -->
<script>
document.getElementById("formReserva").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = new FormData(this);
    const btn = document.querySelector("#formReserva .btn-save");
    btn.disabled = true;
    btn.textContent = "Guardando...";

    try {
        const res = await fetch("../controllers/add_reserva.php", { method:"POST", body:form });
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


<!-- AGREGAR RESERVA A TABLA -->
<script>
function agregarReservaATabla(r){
    const tbody=document.getElementById("tbodyReservas");
    const tr=document.createElement("tr");

    tr.innerHTML=`
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


<!-- ACTUALIZAR HABITACIONES -->
<script>
document.addEventListener("DOMContentLoaded",()=>{

    const fechaIn=document.getElementById("fechaIn");
    const fechaOut=document.getElementById("fechaOut");
    const habSelect=document.getElementById("habitacionSelect");

    async function actualizarHabitaciones(){

        if(!fechaIn.value || !fechaOut.value) return;

        const res=await fetch(`../controllers/habitaciones_disponibles.php?fecha_in=${fechaIn.value}&fecha_out=${fechaOut.value}`);
        const data=await res.json();

        habSelect.innerHTML="";

        if(data.length===0){
            let opt=document.createElement("option");
            opt.textContent="No hay habitaciones disponibles";
            habSelect.appendChild(opt);
            return;
        }

        data.forEach(h=>{
            let opt=document.createElement("option");
            opt.value=h.id;
            opt.textContent=`Hab. ${h.numero} (${h.tipo_nombre}) – Cap: ${h.capacidad}`;
            opt.dataset.tipo=h.tipo_id;
            opt.dataset.precioBase=h.precio_noche;
            habSelect.appendChild(opt);
        });

        window.calcular();
    }

    fechaIn.addEventListener("change", actualizarHabitaciones);
    fechaOut.addEventListener("change", actualizarHabitaciones);
});
</script>


<!-- CALCULAR TOTAL -->
<script>
document.addEventListener("DOMContentLoaded",()=>{

    const habSelect=document.querySelector("#habitacionSelect");
    const extraInput=document.querySelector("#personasExtra");
    const checkIn=document.querySelector("#fechaIn");
    const checkOut=document.querySelector("#fechaOut");
    const descuentoCheckbox=document.getElementById("descuento10");

    const dg_tipo=document.querySelector("#dg_tipo");
    const dg_precio=document.querySelector("#dg_precio");
    const dg_extra=document.querySelector("#dg_extra");
    const dg_noches=document.querySelector("#dg_noches");
    const dg_total=document.querySelector("#dg_total");
    const dg_descuento=document.querySelector("#dg_descuento");
    const totalPrev=document.querySelector("#totalPreview");

    window.calcular=function(){

        if(!habSelect.value) return;

        const selected=habSelect.selectedOptions[0];
        const tipoId=selected.dataset.tipo;

        const ocup=document.querySelector("input[name='ocupacion']:checked").value;
        const extra=parseInt(extraInput.value) || 0;

        let precioBase=0;

        if(window.preciosGlobal &&
           window.preciosGlobal[tipoId] &&
           window.preciosGlobal[tipoId][ocup]){
            precioBase=parseFloat(window.preciosGlobal[tipoId][ocup]);
        } else {
            precioBase=parseFloat(selected.dataset.precioBase || 0);
        }

        dg_tipo.textContent=(ocup==="sencilla") ? "Sencilla" : "Doble";

        const f1=new Date(checkIn.value);
        const f2=new Date(checkOut.value);

        let noches=0;
        if(!isNaN(f1) && !isNaN(f2) && f2>f1){
            noches=(f2-f1)/(1000*60*60*24);
        }

        dg_noches.textContent=noches;

        const costoExtra=extra*100*noches;
        dg_extra.textContent=`$${costoExtra.toFixed(2)}`;

        const subtotal=(precioBase*noches) + costoExtra;

        let totalFinal=subtotal;

        if(descuentoCheckbox.checked){
            totalFinal=subtotal * 0.90;
            dg_descuento.textContent="-10%";
        } else {
            dg_descuento.textContent="—";
        }

        dg_precio.textContent=`$${precioBase.toFixed(2)}`;
        dg_total.textContent=`$${totalFinal.toFixed(2)}`;
        totalPrev.textContent=`Total estimado: $${totalFinal.toFixed(2)}`;
    };

    habSelect.addEventListener("change", window.calcular);
    descuentoCheckbox.addEventListener("change", window.calcular);
    document.querySelectorAll("input[name='ocupacion']")
        .forEach(r => r.addEventListener("change", window.calcular));

    extraInput.addEventListener("input", window.calcular);
    checkIn.addEventListener("change", window.calcular);
    checkOut.addEventListener("change", window.calcular);
});
</script>

<?php include("../views/layout/footer.php"); ?>
