{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Gestión de Usuarios y Roles con buscador en tiempo real a la BD de SAM y Alpine.js autocomplete.
 * @autor          Rubén Alejandro Nolasco Ruiz, Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Antigravity
 * @version        2.0.0
 * @creado         07/05/2026
 * @modificado     24/05/2026
 * @cambios        24/05/2026 - Reesctritura completa con Alpine.js, conexión directa a BD SAM y optimización de caché local.
 */
--}}

@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Roles — GAMA')

@section('content')
<style>
  .usr-main { margin-left: var(--sidebar-width, 260px); min-height: 100vh; background: var(--gama-gris-100); padding: 32px; transition: margin-left var(--transition-normal); }
  .usr-head { margin-bottom: 24px; }
  .usr-title { font-size: 28px; font-weight: 700; color: var(--gama-azul-profundo); margin-bottom: 6px; }
  .usr-sub { color: var(--gama-gris-500); font-size: 14px; }
  
  .usr-card { background: #fff; border: 1px solid var(--gama-gris-200); border-radius: var(--border-radius-lg); overflow: visible; margin-bottom: 24px; box-shadow: var(--shadow-sm); transition: box-shadow var(--transition-fast); }
  .usr-card:hover { box-shadow: var(--shadow-md); }
  .usr-body { padding: 24px; position: relative; }
  
  .autocomplete-container { position: relative; width: 100%; }
  .usr-row { display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: flex-start; }
  
  .usr-input {
    width: 100%;
    border: 1px solid var(--gama-gris-300);
    background: #fff;
    border-radius: var(--border-radius-md);
    padding: 12px 16px;
    font-size: 14px;
    font-family: var(--font-family);
    outline: none;
    transition: all var(--transition-fast);
  }
  .usr-input:focus { border-color: var(--gama-azul-profundo); box-shadow: 0 0 0 3px rgba(19, 68, 116, 0.1); }
  
  /* Autocomplete Dropdown List */
  .autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--gama-gris-300);
    border-radius: var(--border-radius-md);
    margin-top: 6px;
    max-height: 280px;
    overflow-y: auto;
    z-index: 50;
    box-shadow: var(--shadow-lg);
  }
  .autocomplete-item {
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--gama-gris-100);
    transition: background var(--transition-fast);
  }
  .autocomplete-item:last-child { border-bottom: none; }
  .autocomplete-item:hover { background: var(--gama-azul-claro); }
  .autocomplete-name { font-weight: 600; color: var(--gama-azul-profundo); font-size: 14px; }
  .autocomplete-meta { font-size: 12px; color: var(--gama-gris-500); }
  
  /* Employee Card Detail */
  .selected-emp-card {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(232, 244, 252, 0.4) 0%, rgba(255, 255, 255, 1) 100%);
    border: 1px solid var(--gama-azul-claro);
    border-radius: var(--border-radius-md);
    display: grid;
    grid-template-columns: 1fr 200px auto;
    gap: 20px;
    align-items: center;
    animation: fadeIn 0.3s ease-out;
  }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  
  .emp-info-title { font-size: 16px; font-weight: 700; color: var(--gama-azul-profundo); margin-bottom: 4px; }
  .emp-info-sub { font-size: 13px; color: var(--gama-gris-600); }
  
  .usr-select {
    width: 100%;
    border: 1px solid var(--gama-gris-300);
    background: #fff;
    border-radius: var(--border-radius-md);
    padding: 10px 14px;
    font-size: 14px;
    font-family: var(--font-family);
    outline: none;
    cursor: pointer;
  }
  .usr-select:focus { border-color: var(--gama-azul-profundo); }

  .usr-hint { font-size: 12px; color: var(--gama-gris-500); margin-top: 12px; }
  
  /* Table Styles */
  .usr-table-wrap { overflow-x: auto; }
  .usr-table { width: 100%; border-collapse: collapse; font-size: 14px; }
  .usr-table thead th { background: var(--gama-azul-profundo); color: #fff; text-align: left; padding: 14px 16px; font-weight: 600; }
  .usr-table tbody tr { border-bottom: 1px solid var(--gama-gris-200); transition: background var(--transition-fast); }
  .usr-table tbody tr:nth-child(even) { background: var(--gama-gris-100); }
  .usr-table tbody tr:hover { background: rgba(242, 139, 44, 0.05); }
  .usr-table td { padding: 14px 16px; vertical-align: middle; }
  
  /* Badges */
  .role-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
  .role-admin { background: rgba(19, 68, 116, 0.1); color: var(--gama-azul-profundo); }
  .role-docente { background: rgba(242, 139, 44, 0.1); color: var(--gama-naranja); }
  
  .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid var(--gama-gris-300); border-top-color: var(--gama-azul-profundo); border-radius: 50%; animation: spin 0.6s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  
  .usr-empty { padding: 40px; text-align: center; color: var(--gama-gris-500); font-size: 14px; }

  .usr-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
  }

  .btn-sm-fixed {
    height: 34px;
    padding: 0 12px;
    font-size: 12px;
    border-radius: var(--radius-sm, 4px);
  }
  
  @media (max-width: 1024px) {
    .usr-main { margin-left: 0; }
  }
  @media (max-width: 768px) {
    .selected-emp-card { grid-template-columns: 1fr; gap: 16px; }
    .usr-row { grid-template-columns: 1fr; }
  }
</style>

<div class="usr-main" x-data="usuariosRolesApp()">
  <div class="usr-head">
    <h1 class="usr-title">Gestión de Usuarios y Roles</h1>
    <p class="usr-sub">Buscador directo en SAM. Las identidades se almacenan a nivel mínimo local para la autenticación.</p>
  </div>

  <!-- Alerta de Contraseña No Configurada -->
  <template x-if="adminNeedsPassword">
    <div style="background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.4); border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-exclamation-triangle" style="color: #dc2626; font-size: 20px;"></i>
        <div>
          <strong style="color: #1e293b; font-size: 15px; display: block;">¡Contraseña Local de Administrador Requerida!</strong>
          <span style="color: var(--gama-gris-600); font-size: 13px;">Debes configurar tu contraseña de administrador para realizar asignaciones o eliminaciones críticas.</span>
        </div>
      </div>
      <button class="btn btn-primary btn-sm" @click="showSetPasswordModal = true">
        Configurar Contraseña
      </button>
    </div>
  </template>

  <!-- Buscador en tiempo real de SAM -->
  <section class="usr-card">
    <div class="usr-body">
      <div class="usr-row">
        <div class="autocomplete-container" @click.away="showDropdown = false">
          <input 
            type="text" 
            class="usr-input" 
            placeholder="Buscar empleado en la base de datos de SAM (ej: Nombre, Apellidos, Usuario)..."
            x-model="searchTerm"
            @input.debounce.300ms="searchEmployees()"
            @focus="if(results.length > 0) showDropdown = true"
          >
          
          <!-- Spinner de Carga -->
          <div class="absolute right-4 top-4" x-show="loading" style="display: none;">
            <span class="spinner"></span>
          </div>

          <!-- Dropdown Flotante -->
          <div class="autocomplete-dropdown" x-show="showDropdown && results.length > 0" style="display: none;">
            <template x-for="emp in results" :key="emp.externalId">
              <div class="autocomplete-item" @click="selectEmployee(emp)">
                <div>
                  <div class="autocomplete-name" x-text="emp.fullName"></div>
                  <div class="autocomplete-meta">
                    Usuario: <strong x-text="emp.usuario"></strong> | Correo: <span x-text="emp.correo"></span>
                  </div>
                </div>
                <div>
                  <span class="badge badge-activo" style="font-size: 11px;">SAM</span>
                </div>
              </div>
            </template>
          </div>
          
          <div class="autocomplete-dropdown" x-show="showDropdown && results.length === 0 && searchTerm.trim().length >= 2 && !loading" style="display: none;">
            <div class="p-4 text-center text-gray-500 text-sm">
              <i class="fas fa-search-minus mr-2"></i> Sin resultados en la BD de SAM.
            </div>
          </div>
        </div>
      </div>

      <!-- Tarjeta de Empleado Seleccionado -->
      <div class="selected-emp-card" x-show="selectedEmployee !== null" style="display: none;">
        <div>
          <div class="emp-info-title" x-text="selectedEmployee?.fullName"></div>
          <div class="emp-info-sub">
            <i class="far fa-user mr-1"></i> Empleado: <strong x-text="selectedEmployee?.usuario"></strong> | 
            <i class="far fa-envelope class-icon mr-1"></i> Correo: <span x-text="selectedEmployee?.correo"></span>
          </div>
        </div>
        <div>
          <template x-if="isLocalAdmin(selectedEmployee?.externalId)">
            <div style="color: var(--gama-azul-profundo); font-weight: bold; font-size: 14px; padding: 10px 0; display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-user-shield" style="color: var(--gama-azul-profundo);"></i> Administrador (No se puede degradar)
            </div>
          </template>
          <template x-if="!isLocalAdmin(selectedEmployee?.externalId)">
            <select class="usr-select" x-model="selectedRole">
              <option value="teacher">Docente</option>
              <option value="admin">Administrador</option>
            </select>
          </template>
        </div>
        <div>
          <template x-if="!isLocalAdmin(selectedEmployee?.externalId)">
            <button class="btn btn-primary btn-md" @click="assignRole()">
              <i class="fas fa-save mr-1"></i><span>Asignar Rol</span>
            </button>
          </template>
        </div>
      </div>

      <div class="usr-hint">
        <strong>Regla del Sistema (RN-04):</strong> Los alumnos no se gestionan manualmente en el panel; SAM determina automáticamente su estatus durante el login móvil.
      </div>
    </div>
  </section>

  <!-- Listado de Identidades Locales con Detalles SAM -->
  <section class="usr-card">
    <div class="usr-body p-0">
      <div class="usr-table-wrap">
        <table class="usr-table">
          <thead>
            <tr>
              <th style="width: 120px;">N° Empleado</th>
              <th>Nombre completo</th>
              <th>Correo institucional</th>
              <th style="width: 160px;">Rol local</th>
              <th style="width: 120px; text-align: center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loader Tabla -->
            <template x-if="identitiesLoading">
              <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">
                  <span class="spinner mr-2"></span> Cargando usuarios locales con perfiles SAM...
                </td>
              </tr>
            </template>

            <!-- Registros -->
            <template x-if="!identitiesLoading && identities.length > 0">
              <template x-for="identity in identities" :key="identity.externalId">
                <tr>
                  <td><strong x-text="identity.externalId"></strong></td>
                  <td x-text="identity.fullName"></td>
                  <td x-text="identity.email"></td>
                  <td>
                    <span 
                      :class="identity.role === 'admin' ? 'role-badge role-admin' : (identity.role === 'coordinator' ? 'role-badge role-admin' : 'role-badge role-docente')"
                    >
                      <i :class="identity.role === 'admin' ? 'fas fa-user-shield mr-1' : (identity.role === 'coordinator' ? 'fas fa-user-tie mr-1' : (identity.role === 'viewer' ? 'fas fa-eye mr-1' : 'fas fa-chalkboard-teacher mr-1'))"></i>
                      <span x-text="identity.role === 'admin' ? 'Administrador' : (identity.role === 'coordinator' ? 'Coordinador' : (identity.role === 'viewer' ? 'Espectador' : 'Docente'))"></span>
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <div class="usr-actions">
                      <button 
                        class="btn btn-secondary btn-sm btn-sm-fixed" 
                        title="Restablecer a Docente"
                        @click="revokeRole(identity.externalId)"
                        :disabled="identity.role === 'teacher'"
                        x-show="identity.role !== 'admin'"
                      >
                        <i class="fas fa-user-minus"></i>
                      </button>
                      <button 
                        class="btn btn-secondary btn-sm btn-sm-fixed" 
                        style="background:#e3342f;color:#fff;border-color:#e3342f;"
                        title="Eliminar Físicamente"
                        @click="confirmPhysicalDelete(identity.externalId)"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </template>

            <!-- Estado Vacío -->
            <template x-if="!identitiesLoading && identities.length === 0">
              <tr>
                <td colspan="5" class="usr-empty">
                  <i class="fas fa-users-slash text-3xl mb-3 block"></i>
                  No hay usuarios locales registrados en el sistema.
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Modal de Contraseña (Genérico para Confirmación) -->
  <div x-show="showPasswordModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; display: none;">
    <div style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px; max-width: 400px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
      <h3 style="margin-bottom: 12px; font-size: 18px; font-weight: 600; color: #fff;" x-text="passwordModalTitle">Confirmar Acción</h3>
      <p style="font-size: 14px; color: #94a3b8; margin-bottom: 16px;" x-text="passwordModalDesc"></p>
      
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; color: #94a3b8;">Tu contraseña de Administrador</label>
        <input type="password" class="form-input" x-model="confirmPasswordInput" placeholder="Ingresa tu contraseña actual" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #fff;">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button class="btn btn-outline btn-sm" @click="closePasswordModal()">Cancelar</button>
        <button class="btn btn-primary btn-sm" @click="submitPasswordConfirm()">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- Modal para Configurar Contraseña de Admin por primera vez -->
  <div x-show="showSetPasswordModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; display: none;">
    <div style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px; max-width: 400px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
      <h3 style="margin-bottom: 12px; font-size: 18px; font-weight: 600; color: #fff;">Configurar Contraseña Local</h3>
      <p style="font-size: 14px; color: #94a3b8; margin-bottom: 16px;">
        Para realizar acciones críticas (como asignar administradores o eliminar usuarios), debes configurar primero tu contraseña local en este sistema.
      </p>
      
      <div style="margin-bottom: 12px;">
        <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; color: #94a3b8;">Nueva Contraseña</label>
        <input type="password" class="form-input" x-model="newPassword" placeholder="Mínimo 8 caracteres" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #fff;">
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; color: #94a3b8;">Confirmar Contraseña</label>
        <input type="password" class="form-input" x-model="newPassword_confirmation" placeholder="Repite la contraseña" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #fff;">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button class="btn btn-outline btn-sm" @click="showSetPasswordModal = false">Cancelar</button>
        <button class="btn btn-primary btn-sm" @click="savePassword()">Guardar</button>
      </div>
    </div>
  </div>

  <!-- Contenedor global de Toasts -->
  <div class="toast-container" id="toastContainer"></div>
</div>

<script>
  function usuariosRolesApp() {
    return {
      searchTerm: '',
      results: [],
      showDropdown: false,
      loading: false,
      selectedEmployee: null,
      selectedRole: 'teacher',
      identities: [],
      identitiesLoading: false,

      // Propiedades de Seguridad de Contraseña
      adminNeedsPassword: false,
      showSetPasswordModal: false,
      newPassword: '',
      newPassword_confirmation: '',
      showPasswordModal: false,
      passwordModalTitle: '',
      passwordModalDesc: '',
      confirmPasswordInput: '',
      passwordConfirmAction: null,

      init() {
        this.loadIdentities();
        this.checkAdminPasswordStatus();
      },

      isLocalAdmin(extId) {
        if (!extId) return false;
        const local = this.identities.find(i => String(i.externalId) === String(extId));
        return local && local.role === 'admin';
      },

      async checkAdminPasswordStatus() {
        try {
          const resp = await this.apiGet('/api/v1/auth/me');
          this.adminNeedsPassword = !resp.data.hasPassword;
        } catch (err) {
          console.error('Error al consultar estado de contraseña:', err);
        }
      },

      async savePassword() {
        if (!this.newPassword || this.newPassword.length < 8) {
          this.showToast('error', 'La contraseña debe tener al menos 8 caracteres.');
          return;
        }
        if (this.newPassword !== this.newPassword_confirmation) {
          this.showToast('error', 'Las contraseñas no coinciden.');
          return;
        }
        try {
          await this.apiPost('/api/v1/sam-identities/set-password', {
            password: this.newPassword,
            password_confirmation: this.newPassword_confirmation
          });
          this.showToast('success', 'Contraseña configurada con éxito.');
          this.showSetPasswordModal = false;
          this.newPassword = '';
          this.newPassword_confirmation = '';
          this.checkAdminPasswordStatus();
        } catch (err) {
          this.showToast('error', err.message || 'Error al guardar contraseña.');
        }
      },

      closePasswordModal() {
        this.showPasswordModal = false;
        this.confirmPasswordInput = '';
        this.passwordConfirmAction = null;
      },

      submitPasswordConfirm() {
        if (!this.confirmPasswordInput) {
          this.showToast('error', 'Por favor ingresa tu contraseña.');
          return;
        }
        if (this.passwordConfirmAction) {
          this.passwordConfirmAction(this.confirmPasswordInput);
        }
      },

      async loadIdentities() {
        this.identitiesLoading = true;
        try {
          const resp = await this.apiGet('/api/v1/sam-identities');
          this.identities = resp.data || [];
        } catch (err) {
          this.showToast('error', 'Error al cargar usuarios locales.');
        } finally {
          this.identitiesLoading = false;
        }
      },

      async searchEmployees() {
        const q = this.searchTerm.trim();
        if (q.length < 2) {
          this.results = [];
          this.showDropdown = false;
          return;
        }
        this.loading = true;
        try {
          const resp = await this.apiGet('/api/v1/sam/empleados?q=' + encodeURIComponent(q));
          this.results = resp.data || [];
          this.showDropdown = true;
        } catch (err) {
          this.results = [];
          this.showToast('error', err.message || 'Error al buscar en SAM.');
        } finally {
          this.loading = false;
        }
      },

      selectEmployee(emp) {
        this.selectedEmployee = emp;
        this.searchTerm = emp.fullName;
        this.showDropdown = false;
        this.selectedRole = emp.role || 'teacher';
      },

      async assignRole() {
        if (!this.selectedEmployee) {
          this.showToast('error', 'Por favor selecciona un empleado de SAM.');
          return;
        }

        const performAssignment = async (password = null) => {
          try {
            const payload = { role: this.selectedRole };
            if (password) {
              payload.current_password = password;
            }
            await this.apiPost(`/api/v1/sam-identities/${this.selectedEmployee.externalId}/assign-role`, payload);
            this.showToast('success', 'Rol asignado correctamente.');
            this.selectedEmployee = null;
            this.searchTerm = '';
            this.loadIdentities();
            this.closePasswordModal();
          } catch (err) {
            this.showToast('error', err.message || 'Error al asignar el rol.');
          }
        };

        if (this.selectedRole === 'admin') {
          if (this.adminNeedsPassword) {
            this.showToast('error', 'Debes configurar tu contraseña de administrador antes de asignar este rol.');
            this.showSetPasswordModal = true;
            return;
          }
          this.passwordModalTitle = 'Confirmar Asignación de Administrador';
          this.passwordModalDesc = 'Estás a punto de asignar privilegios de Administrador. Confirma con tu contraseña actual.';
          this.passwordConfirmAction = (password) => performAssignment(password);
          this.showPasswordModal = true;
        } else {
          performAssignment();
        }
      },

      async revokeRole(externalId) {
        if (!confirm('¿Confirma revocar el rol especial de este usuario? Se restablecerá a Docente.')) return;
        try {
          await this.apiPost(`/api/v1/sam-identities/${externalId}/assign-role`, {
            role: 'teacher'
          });
          this.showToast('success', 'Rol restablecido a Docente.');
          this.loadIdentities();
        } catch (err) {
          this.showToast('error', err.message || 'Error al revocar el rol.');
        }
      },

      confirmPhysicalDelete(externalId) {
        if (this.adminNeedsPassword) {
          this.showToast('error', 'Debes configurar tu contraseña de administrador antes de eliminar usuarios.');
          this.showSetPasswordModal = true;
          return;
        }
        this.passwordModalTitle = 'Eliminar Usuario Físicamente';
        this.passwordModalDesc = 'Esta acción eliminará de forma física y permanente la identidad seleccionada del sistema local. Confirma con tu contraseña.';
        this.passwordConfirmAction = async (password) => {
          try {
            await this.apiDelete(`/api/v1/sam-identities/${externalId}`, {
              current_password: password
            });
            this.showToast('success', 'Usuario eliminado físicamente.');
            this.loadIdentities();
            this.closePasswordModal();
          } catch (err) {
            this.showToast('error', err.message || 'Error al eliminar el usuario.');
          }
        };
        this.showPasswordModal = true;
      },

      // Fetch Wrapper Helpers
      apiHeaders() {
        const token = localStorage.getItem('auth_token');
        return {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + token
        };
      },

      async apiGet(url) {
        const r = await fetch(url, { method: 'GET', headers: this.apiHeaders() });
        if (r.status === 401) {
          localStorage.clear();
          window.location.href = '/';
          throw new Error('Sesión expirada.');
        }
        const d = await r.json();
        if (!r.ok) throw d;
        return d;
      },

      async apiPost(url, body) {
        const r = await fetch(url, {
          method: 'POST',
          headers: this.apiHeaders(),
          body: JSON.stringify(body)
        });
        if (r.status === 401) {
          localStorage.clear();
          window.location.href = '/';
          throw new Error('Sesión expirada.');
        }
        const d = await r.json();
        if (!r.ok) throw d;
        return d;
      },

      async apiDelete(url, body) {
        const r = await fetch(url, {
          method: 'DELETE',
          headers: this.apiHeaders(),
          body: JSON.stringify(body)
        });
        if (r.status === 401) {
          localStorage.clear();
          window.location.href = '/';
          throw new Error('Sesión expirada.');
        }
        const d = await r.json();
        if (!r.ok) throw d;
        return d;
      },

      showToast(type, msg) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'success' ? 'success' : 'error'}`;
        toast.innerHTML = `
          <div class="toast-icon"><i class="fas ${type === 'success' ? 'fa-check' : 'fa-times'}"></i></div>
          <div class="toast-content">
            <div class="toast-title">${type === 'success' ? 'Éxito' : 'Error'}</div>
            <div class="toast-message">${msg}</div>
          </div>
          <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => toast.remove(), 300);
        }, 4500);
      }
    };
  }
</script>
@endsection
