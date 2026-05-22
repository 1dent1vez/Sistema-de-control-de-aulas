{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Aviso de Privacidad del Sistema de Control de Aulas
 * @autor          Equipo GAMA
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela
 * @mantenimiento  Ghael Garcia Manjarrez
 * @version        1.0.0
 * @creado         19/05/2026
 * @modificado     19/05/2026
 */
--}}

@extends('layouts.app')

@section('title', 'Aviso de Privacidad - G.A.M.A Solutions')

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Aviso de Privacidad</h1>
                <p>Protección y tratamiento de datos personales en el Sistema de Control de Aulas</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="terms-content">
                <p class="intro-text">
                    <i class="fas fa-shield-alt"></i>
                    G.A.M.A. SOLUTIONS S.A. de C.V. (en adelante "GAMA"), con domicilio en la Ciudad de México, es el responsable del tratamiento de sus datos personales. Este Aviso de Privacidad establece los términos y condiciones bajo los cuales se recaban, almacenan, tratan y protegen los datos personales de los usuarios del Sistema de Control de Aulas.
                </p>

                <section class="terms-section">
                    <h2><span class="section-number">1</span> <i class="fas fa-database"></i> Datos Personales Recabados</h2>
                    <p>El sistema recaba las siguientes categorías de datos personales de sus usuarios:</p>
                    <ol class="terms-list numbered-list">
                        <li><strong>Datos de identificación:</strong> nombre completo, número de empleado, correo electrónico institucional.</li>
                        <li><strong>Datos laborales:</strong> puesto, departamento, edificio y aula asignada, rol dentro del sistema (administrador, docente).</li>
                        <li><strong>Datos académicos:</strong> horarios de clase, materias impartidas, grupos asignados.</li>
                        <li><strong>Datos de asistencia:</strong> registros de ausencias docentes, justificantes, fechas y horas de falta.</li>
                        <li><strong>Datos de uso del sistema:</strong> registros de acceso, direcciones IP, marcas de tiempo, endpoints consultados.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">2</span> <i class="fas fa-bullseye"></i> Finalidades del Tratamiento</h2>
                    <p>Los datos personales recabados serán utilizados para las siguientes finalidades:</p>
                    <ol class="terms-list numbered-list">
                        <li>Gestión y control de acceso al Sistema de Control de Aulas.</li>
                        <li>Registro y consulta de horarios de clase y asignación de aulas.</li>
                        <li>Registro de ausencias docentes y generación de reportes.</li>
                        <li>Generación y validación de códigos QR para identificación de aulas.</li>
                        <li>Elaboración de reportes estadísticos y dashboards administrativos.</li>
                        <li>Auditoría y registro de actividades para seguridad del sistema.</li>
                        <li>Cumplimiento de obligaciones legales y regulatorias aplicables.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">3</span> <i class="fas fa-handshake"></i> Transferencia de Datos</h2>
                    <p>GAMA no transferirá sus datos personales a terceros sin su consentimiento, salvo las siguientes excepciones:</p>
                    <ol class="terms-list numbered-list">
                        <li>Autoridades competentes en cumplimiento de un mandato judicial o requerimiento legal.</li>
                        <li>Proveedores de servicios de infraestructura tecnológica necesarios para la operación del sistema (servidores, almacenamiento en la nube, servicios CDN), quienes están sujetos a acuerdos de confidencialidad.</li>
                        <li>La institución educativa contratante, en su calidad de corresponsable del tratamiento de datos de su personal docente.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">4</span> <i class="fas fa-user-lock"></i> Derechos ARCO</h2>
                    <p>Usted tiene derecho a ejercer en cualquier momento sus derechos de:</p>
                    <ol class="terms-list numbered-list">
                        <li><strong>Acceso:</strong> conocer qué datos personales suyos posee GAMA y para qué fines son tratados.</li>
                        <li><strong>Rectificación:</strong> solicitar la corrección de sus datos personales cuando sean inexactos o incompletos.</li>
                        <li><strong>Cancelación:</strong> solicitar la eliminación de sus datos personales cuando considere que no son necesarios para las finalidades señaladas.</li>
                        <li><strong>Oposición:</strong> oponerse al tratamiento de sus datos personales para fines específicos.</li>
                    </ol>
                    <p>Para ejercer sus derechos ARCO, deberá enviar una solicitud por escrito al correo <strong>privacidad@gama.solutions</strong> indicando su nombre, los derechos que desea ejercer y una descripción clara de su solicitud. GAMA dará respuesta en un plazo máximo de 20 días hábiles.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">5</span> <i class="fas fa-clock"></i> Periodo de Conservación</h2>
                    <p>Los datos personales recabados serán conservados durante la vigencia de la relación contractual con la institución educativa y hasta por un periodo adicional de cinco años contados a partir del término de dicha relación, para fines de auditoría y cumplimiento legal. Transcurrido ese periodo, los datos serán eliminados de forma segura mediante procedimientos de borrado irreversible.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">6</span> <i class="fas fa-shield-virus"></i> Medidas de Seguridad</h2>
                    <p>GAMA implementa las siguientes medidas de seguridad para proteger sus datos personales:</p>
                    <ol class="terms-list numbered-list">
                        <li>Cifrado en tránsito mediante TLS 1.2+ para todas las comunicaciones.</li>
                        <li>Cifrado de tokens de autenticación con hash SHA-256.</li>
                        <li>Registro de auditoría de accesos (logs de seguridad) con retención configurable.</li>
                        <li>Rate limiting para prevenir ataques de fuerza bruta.</li>
                        <li>Separación lógica de datos por institución (multi-tenancy).</li>
                        <li>Acceso basado en roles (RBAC) con políticas de autorización granular.</li>
                    </ol>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">7</span> <i class="fas fa-sync-alt"></i> Modificaciones al Aviso de Privacidad</h2>
                    <p>GAMA se reserva el derecho de modificar el presente Aviso de Privacidad en cualquier momento. Las modificaciones entrarán en vigor al momento de su publicación en el sistema y serán notificadas a los usuarios mediante un aviso en la interfaz de inicio de sesión. Se recomienda al usuario revisar periódicamente este aviso para estar informado de cualquier cambio.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">8</span> <i class="fas fa-gavel"></i> Legislación Aplicable</h2>
                    <p>El presente Aviso de Privacidad se rige por lo dispuesto en la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y su Reglamento, así como por cualquier otra legislación aplicable en los Estados Unidos Mexicanos.</p>
                </section>

                <section class="terms-section">
                    <h2><span class="section-number">9</span> <i class="fas fa-calendar-alt"></i> Vigencia</h2>
                    <p>El presente Aviso de Privacidad entra en vigor a partir del 19 de mayo de 2026.</p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
