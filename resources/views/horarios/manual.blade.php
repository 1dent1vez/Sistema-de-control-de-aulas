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
          <label>Docente (búsqueda SAM) *</label>
          <div class="search-box">
            <input type="text" x-model="docenteSearch" @focus="showDocentes=true" @input="showDocentes=true" placeholder="Buscar docente...">
            <div class="search-results" x-show="showDocentes && docentesFiltrados.length > 0" @click.outside="showDocentes=false">
              <template x-for="d in docentesFiltrados" :key="d.id">
                <div class="search-item" @click="selectDocente(d)" x-text="d.nombre"></div>
              </template>
            </div>
          </div>
          <div class="err" x-show="errors.docente" x-text="errors.docente"></div>
        </div>

        <div class="field">
          <label>Materia (semestre activo) *</label>
          <select x-model="form.materiaId">
            <option value="">Selecciona...</option>
            <template x-for="m in materiasSemestre" :key="m.id">
              <option :value="String(m.id)" x-text="m.nombre"></option>
            </template>
          </select>
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

        <button class="btn btn-primary btn-md" style="width:100%;" @click="guardarHorario()">
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
    diasCatalogo: [
      { key: 'LUN', label: 'Lun' }, { key: 'MAR', label: 'Mar' }, { key: 'MIE', label: 'Mie' }, { key: 'JUE', label: 'Jue' }, { key: 'VIE', label: 'Vie' }
    ],
    edificiosActivos: [
      { id: 1, nombre: 'Edificio A' }, { id: 2, nombre: 'Edificio B' }
    ],
    aulas: [
      { id: 101, edificio_id: 1, nombre: 'Aula 101' }, { id: 102, edificio_id: 1, nombre: 'Aula 102' },
      { id: 201, edificio_id: 2, nombre: 'Aula 201' }, { id: 202, edificio_id: 2, nombre: 'Lab C-1' }
    ],
    docentesSAM: [
      { id: 1, nombre: 'Mtra. Laura Mendez' }, { id: 2, nombre: 'Ing. Jose Rivera' }, { id: 3, nombre: 'Mtro. Daniel Rojas' }, { id: 4, nombre: 'Dra. Patricia Luna' }
    ],
    materiasSemestre: [
      { id: 1, nombre: 'Matematicas I' }, { id: 2, nombre: 'Programacion Web' }, { id: 3, nombre: 'Bases de Datos' }
    ],
    horariosActivos: [
      { id: 1, aula_id: 101, dia: 'LUN', hora_inicio: '08:00', hora_fin: '09:30', docente: 'Mtra. Laura Mendez', materia: 'Matematicas I', grupo: '1A' },
      { id: 2, aula_id: 101, dia: 'MIE', hora_inicio: '08:00', hora_fin: '09:30', docente: 'Mtra. Laura Mendez', materia: 'Matematicas I', grupo: '1A' },
      { id: 3, aula_id: 101, dia: 'VIE', hora_inicio: '10:00', hora_fin: '11:30', docente: 'Ing. Jose Rivera', materia: 'Programacion Web', grupo: '3B' }
    ],
    form: {
      edificioId: '',
      aulaId: '',
      docenteId: '',
      materiaId: '',
      grupo: '',
      dias: [],
      horaInicio: '',
      horaFin: ''
    },
    docenteSearch: '',
    showDocentes: false,
    errors: {},
    conflictos: [],
    mensajeError: '',
    mensajeOk: '',
    aulasFiltradas: [],
    init() {
      this.aulasFiltradas = [];
    },
    get docentesFiltrados() {
      const q = this.docenteSearch.trim().toLowerCase();
      if (!q) return this.docentesSAM.slice(0, 6);
      return this.docentesSAM.filter(d => d.nombre.toLowerCase().includes(q)).slice(0, 8);
    },
    get horasDisponibles() {
      const out = [];
      for (let h = 7; h <= 21; h += 1) {
        out.push(String(h).padStart(2, '0') + ':00');
        out.push(String(h).padStart(2, '0') + ':30');
      }
      return out;
    },
    get slotsGrid() {
      return this.horasDisponibles.filter(h => h >= '07:00' && h <= '20:30');
    },
    onEdificioChange() {
      this.form.aulaId = '';
      const id = Number(this.form.edificioId);
      this.aulasFiltradas = this.aulas.filter(a => a.edificio_id === id);
    },
    toggleDia(dia) {
      if (this.form.dias.includes(dia)) this.form.dias = this.form.dias.filter(d => d !== dia);
      else this.form.dias = [...this.form.dias, dia];
    },
    selectDocente(d) {
      this.form.docenteId = String(d.id);
      this.docenteSearch = d.nombre;
      this.showDocentes = false;
    },
    toMin(hhmm) {
      const [h, m] = hhmm.split(':').map(Number);
      return h * 60 + m;
    },
    overlaps(aStart, aEnd, bStart, bEnd) {
      return this.toMin(aStart) < this.toMin(bEnd) && this.toMin(aEnd) > this.toMin(bStart);
    },
    validateBase() {
      this.errors = {};
      this.mensajeError = '';
      this.mensajeOk = '';
      this.conflictos = [];
      if (!this.form.edificioId) this.errors.edificio = 'Selecciona un edificio.';
      if (!this.form.aulaId) this.errors.aula = 'Selecciona un aula.';
      if (!this.form.docenteId) this.errors.docente = 'Selecciona un docente.';
      if (!this.form.materiaId) this.errors.materia = 'Selecciona una materia.';
      if (!this.form.grupo.trim()) this.errors.grupo = 'El grupo es obligatorio.';
      if (this.form.dias.length === 0) this.errors.dias = 'Selecciona al menos un dia.';
      if (!this.form.horaInicio) this.errors.horaInicio = 'Selecciona hora de inicio.';
      if (!this.form.horaFin) this.errors.horaFin = 'Selecciona hora de fin.';
      if (this.form.horaInicio && this.form.horaFin && this.toMin(this.form.horaFin) <= this.toMin(this.form.horaInicio)) {
        this.errors.horaFin = 'La hora fin debe ser mayor a la hora inicio.';
      }
      return Object.keys(this.errors).length === 0;
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
        const detalle = choques.map(c => `${c.dia} ${c.hora_inicio}-${c.hora_fin} (${c.materia} ${c.grupo})`).join(' | ');
        this.mensajeError = `Conflicto detectado con horario activo: ${detalle}`;
        return false;
      }
      return true;
    },
    guardarHorario() {
      if (!this.validateBase()) return;
      if (!this.checkConflictos()) return;
      const docente = this.docentesSAM.find(d => d.id === Number(this.form.docenteId));
      const materia = this.materiasSemestre.find(m => m.id === Number(this.form.materiaId));
      const baseId = Date.now();
      this.form.dias.forEach((dia, idx) => {
        this.horariosActivos.push({
          id: baseId + idx,
          aula_id: Number(this.form.aulaId),
          dia: dia,
          hora_inicio: this.form.horaInicio,
          hora_fin: this.form.horaFin,
          docente: docente ? docente.nombre : '',
          materia: materia ? materia.nombre : '',
          grupo: this.form.grupo.trim()
        });
      });
      this.mensajeOk = 'Horario guardado y asociado al semestre activo.';
      this.showToast('Horario guardado', 'El horario se registro exitosamente.', 'success');
      this.form = { edificioId: this.form.edificioId, aulaId: this.form.aulaId, docenteId: '', materiaId: '', grupo: '', dias: [], horaInicio: '', horaFin: '' };
      this.docenteSearch = '';
      this.errors = {};
      this.conflictos = [];
    },
    horariosEnCelda(dia, slot) {
      const aulaId = Number(this.form.aulaId || 0);
      if (!aulaId) return [];
      const start = slot;
      const endMin = this.toMin(slot) + 30;
      const end = String(Math.floor(endMin / 60)).padStart(2, '0') + ':' + String(endMin % 60).padStart(2, '0');
      return this.horariosActivos.filter(h => h.aula_id === aulaId && h.dia === dia && this.overlaps(start, end, h.hora_inicio, h.hora_fin));
    },
    isConflictCell(dia, slot) {
      if (this.conflictos.length === 0) return false;
      const start = slot;
      const endMin = this.toMin(slot) + 30;
      const end = String(Math.floor(endMin / 60)).padStart(2, '0') + ':' + String(endMin % 60).padStart(2, '0');
      return this.conflictos.some(c => c.dia === dia && this.overlaps(start, end, c.hora_inicio, c.hora_fin));
    },
    showToast(title, message, type) {
      const root = document.getElementById('toastContainer');
      const icon = type === 'success' ? 'check' : 'exclamation';
      const t = document.createElement('div');
      t.className = `toast ${type}`;
      t.innerHTML = `
        <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
        <div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div>
        <button class="toast-close"><i class="fas fa-times"></i></button>
      `;
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
