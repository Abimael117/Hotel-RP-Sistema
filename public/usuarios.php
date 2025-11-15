<?php include("../views/layout/header.php"); ?>

<link rel="stylesheet" href="assets/css/usuarios.css">

<h1>Usuarios</h1>
<p class="page-description">Gestionar roles y cuentas de acceso al panel.</p>

<div class="card">

    <div class="usuarios-header">
        <div>
            <h2>Cuentas de Usuario</h2>
            <p class="subtitle">Gestionar roles y permisos de usuario.</p>
        </div>
        <button class="btn-add" onclick="openUsuarioModalCrear()">
            Añadir Usuario
        </button>
    </div>

    <div class="user-list">
        <div class="user-list-header">
            <span>Usuario</span>
            <span>Rol</span>
            <span></span>
        </div>

        <?php foreach ($usuarios as $u): 
            $iniciales = strtoupper(substr($u['nombre'], 0, 1));
        ?>
        <div class="user-row" 
             data-id="<?= $u['id']; ?>"
             data-nombre="<?= htmlspecialchars($u['nombre'], ENT_QUOTES); ?>"
             data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES); ?>"
             data-rol="<?= htmlspecialchars($u['rol'], ENT_QUOTES); ?>"
        >
            <div class="user-main">
                <div class="user-avatar"><?= $iniciales; ?></div>
                <div class="user-text">
                    <div class="user-name"><?= htmlspecialchars($u['nombre']); ?></div>
                    <div class="user-email"><?= htmlspecialchars($u['email']); ?></div>
                </div>
            </div>

            <div class="user-role">
                <span class="badge <?= $u['rol'] === 'admin' ? 'badge-admin' : 'badge-personal'; ?>">
                    <?= ucfirst($u['rol']); ?>
                </span>
            </div>

            <div class="user-actions">
                <div class="menu-container">
                    <button class="menu-button">⋯</button>
                    <div class="menu-dropdown">
                        <p class="menu-title">Acciones</p>
                        <button class="btn-edit-user">Editar</button>

                        <?php if ($u['id'] != $_SESSION["user"]["id"]): ?>
                        <button class="btn-delete-user menu-delete">Eliminar</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<!-- MODAL CREAR / EDITAR USUARIO -->
<div class="modal-overlay" id="modalUsuario">
    <div class="modal modal-usuario">
        <span class="close-btn" onclick="closeUsuarioModal()">✕</span>
        <h2 id="modalUsuarioTitulo">Añadir Usuario</h2>

        <form id="formUsuario" method="POST" data-mode="create">
            <input type="hidden" name="id" id="userId">

            <label>Nombre completo</label>
            <input type="text" name="nombre" id="userNombre" required>

            <label>Email</label>
            <input type="email" name="email" id="userEmail" required>

            <label>Rol</label>
            <select name="rol" id="userRol" required>
                <option value="admin">Admin</option>
                <option value="personal">Personal</option>
            </select>

            <label>Contraseña</label>
            <input type="password" name="password" id="userPassword"
                   placeholder="Mínimo 6 caracteres">

            <p class="password-hint" id="passwordHintEdit" style="display:none;">
                Si dejas la contraseña vacía, no se cambiará.
            </p>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeUsuarioModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div class="modal-overlay" id="modalDeleteUsuario">
    <div class="modal modal-delete">
        <h3>Eliminar usuario</h3>
        <p>¿Seguro que quieres eliminar este usuario? Esta acción no se puede deshacer.</p>

        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeDeleteUsuarioModal()">Cancelar</button>
            <button class="btn-delete" id="btnConfirmDeleteUsuario">Eliminar</button>
        </div>
    </div>
</div>

<!-- ===== JS MENÚ ⋯ (mismo patrón que habitaciones) ===== -->
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

<!-- ===== JS MODALES USUARIO ===== -->
<script>
const modalUsuario   = document.getElementById("modalUsuario");
const formUsuario    = document.getElementById("formUsuario");
const tituloModal    = document.getElementById("modalUsuarioTitulo");
const passwordHint   = document.getElementById("passwordHintEdit");

const inpId      = document.getElementById("userId");
const inpNombre  = document.getElementById("userNombre");
const inpEmail   = document.getElementById("userEmail");
const selRol     = document.getElementById("userRol");
const inpPass    = document.getElementById("userPassword");

function openUsuarioModalCrear() {
    formUsuario.dataset.mode = "create";
    tituloModal.textContent = "Añadir Usuario";

    inpId.value = "";
    inpNombre.value = "";
    inpEmail.value = "";
    selRol.value = "personal";
    inpPass.value = "";
    inpPass.required = true;
    passwordHint.style.display = "none";

    modalUsuario.style.display = "flex";
}

function openUsuarioModalEditar(row) {
    formUsuario.dataset.mode = "edit";
    tituloModal.textContent = "Editar Usuario";

    inpId.value = row.dataset.id;
    inpNombre.value = row.dataset.nombre;
    inpEmail.value = row.dataset.email;
    selRol.value = row.dataset.rol;
    inpPass.value = "";
    inpPass.required = false;
    passwordHint.style.display = "block";

    modalUsuario.style.display = "flex";
}

function closeUsuarioModal() {
    modalUsuario.style.display = "none";
}
</script>

<!-- ===== JS ELIMINAR USUARIO ===== -->
<script>
let usuarioAEliminarId = null;
const modalDelete = document.getElementById("modalDeleteUsuario");
const btnConfirmDelete = document.getElementById("btnConfirmDeleteUsuario");

function openDeleteUsuarioModal(id) {
    usuarioAEliminarId = id;
    modalDelete.style.display = "flex";
}

function closeDeleteUsuarioModal() {
    usuarioAEliminarId = null;
    modalDelete.style.display = "none";
}

btnConfirmDelete.addEventListener("click", async () => {
    if (!usuarioAEliminarId) return;

    try {
        const res = await fetch("../controllers/delete_usuario.php", {
            method: "POST",
            body: new URLSearchParams({ id: usuarioAEliminarId })
        });
        const data = await res.json();

        if (data.success) {
            const row = document.querySelector(`.user-row[data-id='${usuarioAEliminarId}']`);
            if (row) row.remove();
            showToast("Usuario eliminado");
        } else {
            showToast(data.message || "No se pudo eliminar", true);
        }
    } catch (err) {
        showToast("Error de conexión", true);
    }

    closeDeleteUsuarioModal();
});
</script>

<!-- ===== JS CLICK EN EDITAR / ELIMINAR ===== -->
<script>
document.querySelectorAll(".btn-edit-user").forEach(btn => {
    btn.addEventListener("click", e => {
        const row = e.target.closest(".user-row");
        openUsuarioModalEditar(row);
    });
});

document.querySelectorAll(".btn-delete-user").forEach(btn => {
    btn.addEventListener("click", e => {
        const row = e.target.closest(".user-row");
        openDeleteUsuarioModal(row.dataset.id);
    });
});
</script>

<!-- ===== JS SUBMIT (CREATE / UPDATE) ===== -->
<script>
formUsuario.addEventListener("submit", async function(e) {
    e.preventDefault();

    const mode = formUsuario.dataset.mode; // 'create' o 'edit'
    const url  = (mode === "create")
        ? "../controllers/add_usuario.php"
        : "../controllers/update_usuario.php";

    const formData = new FormData(formUsuario);
    const btn = formUsuario.querySelector(".btn-save");
    btn.disabled = true;
    btn.textContent = "Guardando...";

    try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        if (!data.success) {
            showToast(data.message || "Error al guardar", true);
        } else {
            if (mode === "create") {
                agregarUsuarioALista(data.usuario);
                showToast("Usuario creado ✨");
            } else {
                actualizarUsuarioEnLista(data.usuario);
                showToast("Usuario actualizado ✅");
            }
            closeUsuarioModal();
        }
    } catch (err) {
        console.error(err);
        showToast("Error de conexión", true);
    }

    btn.disabled = false;
    btn.textContent = "Guardar";
});

// Agregar nuevo usuario visualmente
function agregarUsuarioALista(u) {
    const lista = document.querySelector(".user-list");
    const inicial = (u.nombre || "?").charAt(0).toUpperCase();

    const div = document.createElement("div");
    div.className = "user-row";
    div.dataset.id = u.id;
    div.dataset.nombre = u.nombre;
    div.dataset.email = u.email;
    div.dataset.rol = u.rol;

    div.innerHTML = `
        <div class="user-main">
            <div class="user-avatar">${inicial}</div>
            <div class="user-text">
                <div class="user-name">${u.nombre}</div>
                <div class="user-email">${u.email}</div>
            </div>
        </div>
        <div class="user-role">
            <span class="badge ${u.rol === 'admin' ? 'badge-admin' : 'badge-personal'}">
                ${u.rol.charAt(0).toUpperCase() + u.rol.slice(1)}
            </span>
        </div>
        <div class="user-actions">
            <div class="menu-container">
                <button class="menu-button">⋯</button>
                <div class="menu-dropdown">
                    <p class="menu-title">Acciones</p>
                    <button class="btn-edit-user">Editar</button>
                    ${u.esActual ? "" : `<button class="btn-delete-user menu-delete">Eliminar</button>`}
                </div>
            </div>
        </div>
    `;

    lista.appendChild(div);

    // reenganchar eventos
    div.querySelector(".btn-edit-user").addEventListener("click", e => {
        const row = e.target.closest(".user-row");
        openUsuarioModalEditar(row);
    });

    const delBtn = div.querySelector(".btn-delete-user");
    if (delBtn) {
        delBtn.addEventListener("click", e => {
            const row = e.target.closest(".user-row");
            openDeleteUsuarioModal(row.dataset.id);
        });
    }
}

// Actualizar datos en la fila existente
function actualizarUsuarioEnLista(u) {
    const row = document.querySelector(`.user-row[data-id='${u.id}']`);
    if (!row) return;

    row.dataset.nombre = u.nombre;
    row.dataset.email  = u.email;
    row.dataset.rol    = u.rol;

    row.querySelector(".user-name").textContent  = u.nombre;
    row.querySelector(".user-email").textContent = u.email;

    const badge = row.querySelector(".user-role .badge");
    badge.textContent = u.rol.charAt(0).toUpperCase() + u.rol.slice(1);
    badge.className = "badge " + (u.rol === "admin" ? "badge-admin" : "badge-personal");
}
</script>

<?php include("../views/layout/footer.php"); ?>
