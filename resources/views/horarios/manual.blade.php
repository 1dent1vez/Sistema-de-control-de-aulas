{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Horarios Manuales - Proyecto B: Sistema de Control de Aulas
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.0.0
 * @creado         07/05/2026
 * @modificado     07/05/2026
 */
--}}

@extends('layouts.app')

@section('title', 'Horarios Manuales - GAMA Solutions')

@section('content')
<style>
  .manual-main {
    margin-left: var(--sidebar-width, 240px);
    min-height: 100vh;
    background: var(--ice-blue);
    padding: 28px 32px;
  }
  .manual-header { margin-bottom: 16px; }
  .manual-title { font-size: 26px; font-weight: 700; color: var(--midnight); margin-bottom: 4px; }
  .manual-subtitle { color: var(--soft-steel); font-size: 14px; }
  .manual-grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 16px;
    align-items: start;
  }
  .panel {
    background: #fff;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }
  .panel-head {
    padding: 14px 16px;
    border-bottom: 1px solid var(--mist-blue);
    background: #fff;
  }
  .panel-title { font-size: 16px; font-weight: 700; color: var(--midnight); }
  .panel-body { padding: 16px; }
  .field { margin-bottom: 12px; }
  .field label { display:block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--midnight); }
  .field input, .field select {
    width: 100%;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    background: var(--ice-blue);
    padding: 10px 12px;
    font-size: 14px;
    font-family: var(--font-main);
    outline: none;
  }
  .field input:focus, .field select:focus { border-color: var(--corp-orange); }
  .err { color: #d12b2b; font-size: 12px; margin-top: 5px; }
  .days-wrap { display: flex; gap: 8px; flex-wrap: wrap; }
  .day-chip {
    border: 1px solid var(--mist-blue);
    background: #fff;
    color: var(--dark-graphite);
    border-radius: 18px;
    padding: 6px 11px;
    font-size: 12px;
    cursor: pointer;
    user-select: none;
  }
  .day-chip.active {
    background: var(--deep-blue);
    border-color: var(--deep-blue);
    color: #fff;
  }
  .search-box { position: relative; }
  .search-results {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 4px);
    background: #fff;
    border: 1px solid var(--mist-blue);
    border-radius: var(--radius-md);
    max-height: 170px;
    overflow-y: auto;
    z-index: 20;
  }
  .search-item {
    padding: 8px 10px;
    font-size: 13px;
    cursor: pointer;
    border-bottom: 1px solid var(--mist-blue);
  }
  .search-item:hover { background: var(--light-orange); }
  .grid-wrap { overflow-x: auto; }
  .week-grid {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }
  .week-grid thead th {
    background: var(--deep-blue);
    color: #fff;
    padding: 10px 8px;
    text-align: center;
    min-width: 120px;
  }
  .week-grid thead th:first-child { min-width: 88px; }
  .week-grid tbody td {
    border: 1px solid var(--mist-blue);
    padding: 10px 8px;
    background: #fff;
    vertical-align: top;
    min-height: 56px;
  }
  .week-grid tbody tr:nth-child(even) td { background: var(--ice-blue); }
  .hour-col { font-weight: 600; color: var(--deep-blue); text-align: center; }
  .slot-item {
    background: rgba(19,68,116,0.08);
    border: 1px solid rgba(19,68,116,0.2);
    border-radius: 8px;
    padding: 6px;
    line-height: 1.35;
    font-size: 12px;
    color: var(--midnight);
  }
  .slot-item + .slot-item { margin-top: 6px; }
  .conflict-cell {
    outline: 2px solid #FF0000;
    outline-offset: -2px;
    background: rgba(255,0,0,0.08) !important;
  }
  .note {
    margin-top: 8px;
    padding: 10px 12px;
    border-radius: var(--radius-md);
    font-size: 13px;
  }
  .note.error { background: rgba(255,0,0,0.08); color: #B00000; border: 1px solid rgba(255,0,0,0.35); }
  .note.ok { background: rgba(90,154,90,0.12); color: var(--status-active); border: 1px solid rgba(90,154,90,0.35); }
  @media (max-width: 1100px) { .manual-grid { grid-template-columns: 1fr; } .manual-main { margin-left: 0; } }
</style>

<div class="manual-main" x-data="pant05HorarioManual()">
  <div class="manual-header">
    <h1 class="manual-title">Horarios Manuales</h1>
    <p class="manual-subtitle">Formulario manual + grilla semanal de referencia para prevenir empalmes.</p>
  </div>

  <div class="manual-grid">
    <section class="panel">
      <div class="panel-head"><div class="panel-title">Registrar Horario</div></div>
      <div class="panel-body">
        <div class="field">
          <label>Edificio *</label>
          <select x-model="form.edificioId" @change="onEdificioChange()">
            <option value="">Selecciona...</option>
            <template x-for="e in edificiosActivos" :key="e.id">
              <option :value="String(e.id)" x-text="e.nombre"></option>
            </template>
          </select>
          <div class="err" x-show="errors.edificio" x-text="errors.edificio"></div>
        </div>

        <div class="field">
          <label>Aula *</label>
          <select x-model="form.aulaId">
            <option value="">Selecciona...</option>
            <template x-for="a in aulasFiltradas" :key="a.id">
              <option :value="String(a.id)" x-text="a.nombre"></option>
            </template>
          </select>
          <div class="err" x-show="errors.aula" x-text="errors.aula"></div>
        </div>

        <div class="field">
          <label>ID Docente (SAM) *</label>
          <input type="text" x-model="form.docenteExterno" placeholder="Ej. SAM-00123" autocomplete="off">
          <div class="err" x-show="errors.docente" x-text="errors.docente"></div>
        </div>

        <div class="field">
          <label>Materia *</label>
          <input type="text" x-model="form.subjectName" placeholder="Ej. Matemáticas I" autocomplete="off">
          <div class="err" x-show="errors.materia" x-text="errors.materia"></div>
        </div>

        <div class="field">
          <label>Grupo *</label>
          <input type="text" x-model="form.grupo" placeholder="Ej. 3A">
          <div class="err" x-show="errors.grupo" x-text="errors.grupo"></div>
        </div>

        <div class="field">
          <label>Día(s) de la semana *</label>
          <div class="days-wrap">
            <template x-for="d in diasCatalogo" :key="d.key">
              <button type="button" class="day-chip" :class="{ 'active': form.dias.includes(d.key) }" @click="toggleDia(d.key)" x-text="d.label"></button>
            </template>
          </div>
          <div class="err" x-show="errors.dias" x-text="errors.dias"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div class="field">
            <label>Hora inicio *</label>
            <select x-model="form.horaInicio">
              <option value="">Selecciona...</option>
              <template x-for="h in horasDisponibles" :key="'i'+h">
                <option :value="h" x-text="h"></option>
              </template>
            </select>
            <div class="err" x-show="errors.horaInicio" x-text="errors.horaInicio"></div>
          </div>
          <div class="field">
            <label>Hora fin *</label>
            <select x-model="form.horaFin">
              <option value="">Selecciona...</option>
              <template x-for="h in horasDisponibles" :key="'f'+h">
                <option :value="h" x-text="h"></option>
              </template>
            </select>
            <div class="err" x-show="errors.horaFin" x-text="errors.horaFin"></div>
          </div>
        </div>

        <button class="btn btn-primary btn-md" style="width:100%;" @click="guardarHorario()" :disabled="saving">
          <i class="fas fa-save"></i>
          <span>Guardar Horario</span>
        </button>

        <div class="note error" x-show="mensajeError" x-text="mensajeError"></div>
        <div class="note ok" x-show="mensajeOk" x-text="mensajeOk"></div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><div class="panel-title">Grilla semanal de referencia (solo lectura)</div></div>
      <div class="panel-body">
        <div class="grid-wrap">
          <table class="week-grid">
            <thead>
              <tr>
                <th>Hora</th>
                <template x-for="d in diasCatalogo" :key="'h'+d.key"><th x-text="d.label"></th></template>
              </tr>
            </thead>
            <tbody>
              <template x-for="slot in slotsGrid" :key="'r'+slot">
                <tr>
                  <td class="hour-col" x-text="slot"></td>
                  <template x-for="d in diasCatalogo" :key="'c'+slot+d.key">
                    <td :class="{ 'conflict-cell': isConflictCell(d.key, slot) }">
                      <template x-for="item in horariosEnCelda(d.key, slot)" :key="item.id">
                        <div class="slot-item">
                          <strong x-text="item.hora_inicio + ' - ' + item.hora_fin"></strong><br>
                          <span x-text="item.materia"></span><br>
                          <span x-text="item.grupo + ' · ' + item.docente"></span>
                        </div>
                      </template>
                    </td>
                  </template>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <div class="toast-container" id="toastContainer"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function pant05HorarioManual() {
  return {
    /* â”€â”€ Catálogos cargados desde API â”€â”€ */
    diasCatalogo: [
      { key: 'monday',    label: 'Lun' },
      { key: 'tuesday',   label: 'Mar' },
      { key: 'wednesday', label: 'Mié' },
      { key: 'thursday',  label: 'Jue' },
      { key: 'friday',    label: 'Vie' },
    ],
    edificiosActivos: [],
    aulas: [],
    semesterActivo: null,
    horariosActivos: [],

    /* â”€â”€ Formulario â”€â”€ */
    form: {
      edificioId: '', aulaId: '', docenteExterno: '',
      subjectName: '', grupo: '', dias: [], horaInicio: '', horaFin: ''
    },
    docenteSearch: '',
    showDocentes: false,
    errors: {},
    conflictos: [],
    mensajeError: '',
    mensajeOk: '',
    aulasFiltradas: [],
    loadingGrid: false,
    saving: false,

    /* â”€â”€ Inicialización â”€â”€ */
    async init() {
      await this.loadCatalogos();
    },

    getCsrf() {
      return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    async apiFetch(url, opts = {}) {
      const res = await fetch(url, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.getCsrf(),
          ...(opts.headers ?? {}),
        },
        ...opts,
      });
      const json = await res.json();
      if (!res.ok) throw { status: res.status, json };
      return json;
    },

    async loadCatalogos() {
      try {
        const [buildRes, semRes] = await Promise.all([
          this.apiFetch('/api/v1/buildings'),
          this.apiFetch('/api/v1/semesters/current'),
        ]);
        this.edificiosActivos = (buildRes.data ?? [])
          .filter(b => b.isActive)
          .map(b => ({ id: b.id, nombre: b.name }));

        this.semesterActivo = semRes.data ?? null;

        if (this.semesterActivo) {
          const schedRes = await this.apiFetch(`/api/v1/class-schedules?semester_id=${this.semesterActivo.id}`);
          this.horariosActivos = (schedRes.data ?? []).map(s => ({
            id:          s.id,
            aula_id:     s.classroomId,
            dia:         s.weekday,
            hora_inicio: s.startTime,
            hora_fin:    s.endTime,
            docente:     s.teacherExternalId,
            materia:     s.subjectName,
            grupo:       s.groupLabel ?? '',
          }));
        }

        const aulaRes = await this.apiFetch('/api/v1/classrooms');
        this.aulas = (aulaRes.data ?? [])
          .filter(c => c.isActive)
          .map(c => ({ id: c.id, edificio_id: c.buildingId, nombre: c.classroomName }));

      } catch(e) {
        this.mensajeError = 'Error al cargar catálogos desde la API.';
      }
    },

    async onEdificioChange() {
      this.form.aulaId = '';
      const id = Number(this.form.edificioId);
      this.aulasFiltradas = this.aulas.filter(a => a.edificio_id === id);

      if (id && this.form.aulaId === '') {
        this.horariosActivos = [];
        if (this.semesterActivo) {
          try {
            const res = await this.apiFetch(
              `/api/v1/class-schedules?semester_id=${this.semesterActivo.id}&building_id=${id}`
            );
            this.horariosActivos = (res.data ?? []).map(s => ({
              id:          s.id,
              aula_id:     s.classroomId,
              dia:         s.weekday,
              hora_inicio: s.startTime,
              hora_fin:    s.endTime,
              docente:     s.teacherExternalId,
              materia:     s.subjectName,
              grupo:       s.groupLabel ?? '',
            }));
          } catch(e) { /* silencioso */ }
        }
      }
    },

    /* â”€â”€ Horas disponibles â”€â”€ */
    get horasDisponibles() {
      const out = [];
      for (let h = 7; h <= 21; h++) {
        out.push(String(h).padStart(2, '0') + ':00');
        out.push(String(h).padStart(2, '0') + ':30');
      }
      return out;
    },
    get slotsGrid() {
      return this.horasDisponibles.filter(h => h >= '07:00' && h <= '20:30');
    },

    toggleDia(dia) {
      if (this.form.dias.includes(dia))
        this.form.dias = this.form.dias.filter(d => d !== dia);
      else
        this.form.dias = [...this.form.dias, dia];
    },

    toMin(hhmm) {
      const [h, m] = hhmm.split(':').map(Number);
      return h * 60 + m;
    },
    overlaps(aS, aE, bS, bE) {
      return this.toMin(aS) < this.toMin(bE) && this.toMin(aE) > this.toMin(bS);
    },

    validateBase() {
      this.errors = {};
      this.mensajeError = '';
      this.mensajeOk = '';
      this.conflictos = [];
      if (!this.form.edificioId)     this.errors.edificio    = 'Selecciona un edificio.';
      if (!this.form.aulaId)         this.errors.aula        = 'Selecciona un aula.';
      if (!this.form.docenteExterno.trim()) this.errors.docente = 'El ID de docente es obligatorio.';
      if (!this.form.subjectName.trim()) this.errors.materia  = 'El nombre de materia es obligatorio.';
      if (!this.form.grupo.trim())   this.errors.grupo       = 'El grupo es obligatorio.';
      if (this.form.dias.length === 0) this.errors.dias      = 'Selecciona al menos un día.';
      if (!this.form.horaInicio)     this.errors.horaInicio  = 'Selecciona hora de inicio.';
      if (!this.form.horaFin)        this.errors.horaFin     = 'Selecciona hora de fin.';
      if (this.form.horaInicio && this.form.horaFin &&
          this.toMin(this.form.horaFin) <= this.toMin(this.form.horaInicio))
        this.errors.horaFin = 'La hora fin debe ser mayor a la hora inicio.';
      if (!this.semesterActivo) this.mensajeError = 'No hay semestre activo. Crea uno primero.';
      return Object.keys(this.errors).length === 0 && !!this.semesterActivo;
    },

    checkConflictos() {
      const aulaId = Number(this.form.aulaId);
      const diaSet = new Set(this.form.dias);
      const choques = this.horariosActivos.filter(h =>
        h.aula_id === aulaId &&
        diaSet.has(h.dia) &&
        this.overlaps(this.form.horaInicio, this.form.horaFin, h.hora_inicio, h.hora_fin)
      );
      this.conflictos = choques;
      if (choques.length > 0) {
        const det = choques.map(c => `${c.dia} ${c.hora_inicio}-${c.hora_fin} (${c.materia})`).join(' | ');
        this.mensajeError = `Conflicto detectado: ${det}`;
        return false;
      }
      return true;
    },

    async guardarHorario() {
      if (!this.validateBase()) return;
      if (!this.checkConflictos()) return;
      this.saving = true;

      const saved = [];
      try {
        for (const dia of this.form.dias) {
          const res = await this.apiFetch('/api/v1/class-schedules', {
            method: 'POST',
            body: JSON.stringify({
              semester_id:          this.semesterActivo.id,
              classroom_id:         Number(this.form.aulaId),
              teacher_external_id:  this.form.docenteExterno.trim(),
              subject_name:         this.form.subjectName.trim(),
              group_label:          this.form.grupo.trim(),
              weekday:              dia,
              start_time:           this.form.horaInicio,
              end_time:             this.form.horaFin,
            }),
          });
          if (res.data) {
            this.horariosActivos.push({
              id:          res.data.id,
              aula_id:     res.data.classroomId,
              dia:         res.data.weekday,
              hora_inicio: res.data.startTime,
              hora_fin:    res.data.endTime,
              docente:     res.data.teacherExternalId,
              materia:     res.data.subjectName,
              grupo:       res.data.groupLabel ?? '',
            });
          }
        }
        this.mensajeOk = 'Horario guardado y asociado al semestre activo.';
        this.showToast('Horario guardado', 'El horario se registró exitosamente.', 'success');
        this.form = {
          edificioId: this.form.edificioId,
          aulaId: this.form.aulaId,
          docenteExterno: '', subjectName: '', grupo: '',
          dias: [], horaInicio: '', horaFin: '',
        };
        this.errors = {};
        this.conflictos = [];
      } catch(err) {
        const errs = err.json?.errors ?? {};
        const first = Object.values(errs)[0];
        this.mensajeError = first?.[0] ?? (err.json?.message ?? 'Error al guardar el horario.');
      } finally {
        this.saving = false;
      }
    },

    horariosEnCelda(dia, slot) {
      const aulaId = Number(this.form.aulaId || 0);
      if (!aulaId) return [];
      const endMin = this.toMin(slot) + 30;
      const end = String(Math.floor(endMin / 60)).padStart(2, '0') + ':' + String(endMin % 60).padStart(2, '0');
      return this.horariosActivos.filter(h =>
        h.aula_id === aulaId && h.dia === dia && this.overlaps(slot, end, h.hora_inicio, h.hora_fin)
      );
    },

    isConflictCell(dia, slot) {
      if (this.conflictos.length === 0) return false;
      const endMin = this.toMin(slot) + 30;
      const end = String(Math.floor(endMin / 60)).padStart(2, '0') + ':' + String(endMin % 60).padStart(2, '0');
      return this.conflictos.some(c => c.dia === dia && this.overlaps(slot, end, c.hora_inicio, c.hora_fin));
    },

    showToast(title, message, type) {
      const root = document.getElementById('toastContainer');
      const icon = type === 'success' ? 'check' : 'exclamation';
      const t = document.createElement('div');
      t.className = `toast ${type}`;
      t.innerHTML = `
        <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
        <div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div>
        <button class="toast-close"><i class="fas fa-times"></i></button>`;
      root.appendChild(t);
      setTimeout(() => t.classList.add('show'), 10);
      const rm = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 260); };
      const timer = setTimeout(rm, 4200);
      t.querySelector('.toast-close').addEventListener('click', () => { clearTimeout(timer); rm(); });
    }
  }
}
</script>
@endsection
