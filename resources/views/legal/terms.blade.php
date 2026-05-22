{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Términos y Condiciones de uso del Sistema de Control de Aulas
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.1.0
 * @creado         11/04/2026
 * @modificado     19/05/2026
 * @cambios        19/05/2026 - Eliminadas referencias a alumnos, membresías y planes SaaS.
 *                              Alcance real: edificios, aulas, horarios, ausencias docentes, QR.
 */
--}}

@extends('layouts.app')

@section('title', 'Términos y Condiciones - G.A.M.A Solutions')

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Términos y Condiciones de Uso</h1>
                <p>Condiciones generales para el uso del Sistema de Control de Aulas</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="terms-content">
                <p class="intro-text">
                    <i class="fas fa-info-circle"></i>
                    Al acceder o utilizar el Sistema de Control de Aulas, el usuario acepta en su totalidad los presentes Términos y Condiciones. Si no está de acuerdo con alguno de ellos, deberá abstenerse de utilizar el sistema.
                </p>

                <section class="terms-section">
                    <h2><span class="section-number">1</span> <i class="fas fa-book"></i> Definiciones</h2>
                    <ol class="definitions-list numbered-list">
                        <li><strong>"Sistema":</strong> el Sistema de Control de Aulas, plataforma web desarrollada por G.A.M.A. Solutions para la gestión de infraestructura, horarios y personal docente en instituciones educativas.</li>
                        <li><strong>"Institución":</strong> la entidad educativa contratante del Sistema.</li>
                        <li><strong>"Edificio":</strong> unidad inmobiliaria registrada en el Sistema como contenedora de niveles y aulas.</li>
                        <li><strong>"Aula":</strong> espacio físico (salón de clases, laboratorio) registrado en el Sistema con un tipo específico (aula, laboratorio de cómputo).</li>
                        <li><strong>"Horario":</strong> asignación de una materia, docente, grupo y aula en un día y hora específicos dentro de un semestre.</li>
                        <li><strong>"Semestre":</strong> periodo académico definido por la Institución con fechas de inicio y fin.</li>
                        <li><strong>"QR":</strong> código de respuesta rápida generado por el Sistema para identificación de aulas.</li>
                        <li><strong>"Ausencia Docente":</strong> registro de falta de un docente en su horario asignado, con tipo, fechas y opcionalmente justificante.</li>
                        <li><strong>"Rol":</strong> perfil de permisos asignado a un usuario (administrador o docente) que determina los módulos y acciones a los que tiene acceso.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">2</span> <i class="fas fa-key"></i> Condiciones de Acceso</h2>
                    <ol class="terms-list numbered-list">
                        <li>El acceso al Sistema está restringido a usuarios autorizados por la Institución contratante.</li>
                        <li>El usuario es responsable de mantener la confidencialidad de sus credenciales de acceso. G.A.M.A. Solutions no almacena contraseñas en texto plano ni en formato reversible.</li>
                        <li>En caso de compromiso de credenciales, el usuario deberá notificar inmediatamente al administrador del Sistema.</li>
                        <li>G.A.M.A. Solutions se reserva el derecho de suspender el acceso a cualquier usuario que incumpla estos Términos.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">3</span> <i class="fas fa-check-circle"></i> Uso Aceptable</h2>
                    <p>El usuario se compromete a:</p>
                    <ol class="terms-list numbered-list">
                        <li>Utilizar el Sistema exclusivamente para los fines institucionales y administrativos para los que fue contratado.</li>
                        <li>No intentar acceder a módulos, datos o funcionalidades para los cuales no tenga autorización según su rol asignado.</li>
                        <li>No realizar ingeniería inversa, descompilación ni análisis no autorizado del código fuente o infraestructura.</li>
                        <li>No sobrecargar intencionalmente los servidores superando los límites de peticiones establecidos.</li>
                        <li>No subir archivos con código malicioso, malware o contenido dañino.</li>
                        <li>Reportar vulnerabilidades de seguridad al equipo de G.A.M.A. Solutions en lugar de explotarlas.</li>
                        <li>No registrar ausencias docentes falsas o modificar registros históricos sin autorización.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">4</span> <i class="fas fa-copyright"></i> Propiedad Intelectual</h2>
                    <p>Todos los componentes del Sistema —código fuente, diseño de interfaz, bases de datos, algoritmos y documentación— son propiedad exclusiva de G.A.M.A. Solutions. La Institución obtiene una licencia de uso limitada, no exclusiva y no transferible. Queda prohibida la reproducción, distribución o modificación sin autorización escrita.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">5</span> <i class="fas fa-server"></i> Disponibilidad y Limitación de Responsabilidad</h2>
                    <ol class="terms-list numbered-list">
                        <li>G.A.M.A. Solutions realizará sus mejores esfuerzos para mantener la disponibilidad continua del Sistema, sin garantizar disponibilidad ininterrumpida.</li>
                        <li>Las ventanas de mantenimiento se notificarán con al menos 24 horas de anticipación cuando sea posible.</li>
                        <li>G.A.M.A. Solutions no será responsable por pérdida de datos por uso inadecuado, daños indirectos, ni por fallas en servicios de autenticación institucionales externos (SAM).</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">6</span> <i class="fas fa-edit"></i> Modificaciones a los Términos</h2>
                    <p>Cualquier modificación entrará en vigor al momento de su publicación. Los usuarios serán notificados mediante un aviso en la interfaz del Sistema y deberán aceptar explícitamente antes de continuar. El uso continuado implica la aceptación de los términos modificados.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">7</span> <i class="fas fa-gavel"></i> Legislación Aplicable</h2>
                    <p>Los presentes Términos se rigen por la legislación vigente en los Estados Unidos Mexicanos. Las partes se someten a la jurisdicción de los tribunales competentes en la Ciudad de México, renunciando a cualquier otro fuero que pudiera corresponderles.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">8</span> <i class="fas fa-users-cog"></i> Responsabilidades por Rol</h2>

                    <h3><span class="subsection-number">8.1</span> <i class="fas fa-chalkboard-teacher"></i> Docente</h3>
                    <ol class="terms-list numbered-list">
                        <li>Es responsable de la veracidad de los registros de ausencia que capture en el Sistema.</li>
                        <li>Debe reportar cualquier inconsistencia en sus horarios asignados al administrador del Sistema.</li>
                        <li>El uso del módulo de estatus docente es personal e intransferible.</li>
                    </ol>

                    <h3><span class="subsection-number">8.2</span> <i class="fas fa-user-shield"></i> Administrador</h3>
                    <ol class="terms-list numbered-list">
                        <li>Es responsable de la correcta configuración de edificios, aulas, niveles y horarios en el Sistema.</li>
                        <li>Es responsable de gestionar los roles y permisos de los usuarios de su Institución.</li>
                        <li>Puede consultar el historial completo de ausencias docentes, horarios y reportes del Sistema.</li>
                        <li>Es responsable de mantener actualizados los datos institucionales y semestres académicos.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">9</span> <i class="fas fa-layer-group"></i> Alcance del Sistema</h2>
                    <ol class="terms-list numbered-list">
                        <li>El Sistema de Control de Aulas comprende los módulos de: catálogos base (instituciones, tipos de ausencia), edificios y aulas, horarios y semestres, estatus docente (ausencias), códigos QR, y dashboard administrativo.</li>
                        <li>El Sistema no incluye funcionalidades de registro de asistencia de alumnos, gestión de calificaciones, ni comunicación directa con estudiantes.</li>
                        <li>Los datos ingresados al Sistema son propiedad de la Institución contratante. G.A.M.A. Solutions actúa únicamente como encargado del tratamiento.</li>
                    </ol>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
.terms-content { max-width: none; }
.terms-content .intro-text { background: var(--ice-blue); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--corp-orange); margin-bottom: 32px; font-size: 16px; line-height: 1.6; }
.terms-content .intro-text i { color: var(--corp-orange); margin-right: 12px; }
.terms-section { margin-bottom: 40px; }
.terms-section h2 { font-size: 24px; font-weight: 600; color: var(--midnight); margin-bottom: 16px; display: flex; align-items: center; gap: 12px; }
.terms-section h2 .section-number { background: var(--deep-blue); color: white; padding: 4px 8px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 700; min-width: 40px; text-align: center; }
.terms-section h3 .subsection-number { background: var(--royal-blue); color: white; padding: 2px 6px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; min-width: 30px; text-align: center; }
.terms-section h2 i { color: var(--deep-blue); font-size: 20px; }
.terms-section h3 { font-size: 20px; font-weight: 600; color: var(--royal-blue); margin: 24px 0 12px 0; display: flex; align-items: center; gap: 10px; }
.terms-section h3 i { color: var(--corp-orange); font-size: 18px; }
.terms-section p { margin-bottom: 16px; line-height: 1.6; color: var(--dark-graphite); }
.definitions-list, .terms-list { list-style: none; padding: 0; margin: 0; }
.definitions-list li, .terms-list li { padding: 8px 0; padding-left: 32px; position: relative; line-height: 1.6; color: var(--dark-graphite); }
.definitions-list li:before, .terms-list li:before { content: "•"; color: var(--corp-orange); font-weight: bold; position: absolute; left: 0; font-size: 18px; }
.numbered-list { counter-reset: item-counter; }
.numbered-list li:before { content: counter(item-counter) "."; counter-increment: item-counter; color: var(--deep-blue); font-weight: 600; font-size: 14px; }
.definitions-list li strong { color: var(--midnight); font-weight: 600; }
</style>
@endsection
