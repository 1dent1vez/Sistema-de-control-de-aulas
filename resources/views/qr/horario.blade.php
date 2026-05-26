{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Vista pública del horario completo de un aula consultado vía QR.
 * @autor          Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 * @autorizador    Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 * @prueba         Diego Miguel Hernandez Fabela <correo@dominio.com>
 * @mantenimiento  Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 * @version        1.0.0
 * @creado         2026-05-26
 * @modificado     2026-05-26
 * @cambios        2026-05-26 - Creación inicial de la vista de horario público QR
 */
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario de Aula — {{ $aula->classroom_name }}</title>
    <!-- Google Fonts: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            /* Colors matching GAMA palette */
            --deep-blue: #134474;
            --royal-blue: #1E5A8A;
            --soft-steel: #5F86A6;
            --ice-blue: #F2F7FB;
            --mist-blue: #E3EDF5;
            --light-blue: #E8F1F8;
            --dark-graphite: #545454;
            --midnight: #1F2A35;
            
            --corp-orange: #F28B2C;
            --deep-orange: #D96A10;
            --light-orange: #FFE8D6;

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            
            --shadow-sm: 0 2px 4px rgba(19, 68, 116, 0.04);
            --shadow-md: 0 8px 16px rgba(19, 68, 116, 0.08);
            --shadow-lg: 0 16px 32px rgba(19, 68, 116, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--ice-blue) 0%, #E9F1F7 100%);
            color: var(--dark-graphite);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Header logo bar */
        .gama-logo-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 14px 24px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: var(--shadow-sm);
        }

        .gama-logo-bar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .gama-logo-bar .brand i {
            color: var(--deep-blue);
            font-size: 24px;
        }

        .gama-logo-bar .brand h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--midnight);
            letter-spacing: -0.5px;
        }

        .gama-logo-bar .time-badge {
            font-size: 13px;
            color: var(--soft-steel);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Classroom Title Card */
        .classroom-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 32px;
            border: 1px solid var(--mist-blue);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .classroom-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 6px;
            background: var(--deep-blue);
        }

        .classroom-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .classroom-icon {
            width: 64px;
            height: 64px;
            background: var(--light-blue);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--deep-blue);
            font-size: 28px;
        }

        .classroom-details h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--midnight);
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .classroom-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .meta-item {
            font-size: 14px;
            color: var(--soft-steel);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-item i {
            color: var(--royal-blue);
        }

        .semester-badge {
            background: var(--light-orange);
            color: var(--deep-orange);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(242, 139, 44, 0.2);
        }

        /* Schedule Table section */
        .schedule-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--mist-blue);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 15px;
        }

        .schedule-table th {
            background: var(--deep-blue);
            color: white;
            font-weight: 600;
            padding: 16px 20px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .schedule-table td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--mist-blue);
            vertical-align: middle;
        }

        .schedule-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .schedule-table tbody tr:nth-child(even) {
            background-color: var(--ice-blue);
        }

        .schedule-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .schedule-table tbody tr:hover {
            background-color: var(--light-orange);
        }

        .day-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 13px;
            background: var(--light-blue);
            color: var(--deep-blue);
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .time-text {
            font-weight: 600;
            color: var(--midnight);
            white-space: nowrap;
        }

        .subject-text {
            font-weight: 700;
            color: var(--midnight);
            font-size: 15px;
        }

        .teacher-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .teacher-avatar {
            width: 28px;
            height: 28px;
            background: var(--mist-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--deep-blue);
        }

        .teacher-name {
            font-weight: 500;
        }

        .group-badge {
            background: #EAF7EB;
            color: #2E7D32;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid rgba(46, 125, 50, 0.15);
            display: inline-block;
        }

        /* Error States & Alerts */
        .alert-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--mist-blue);
            box-shadow: var(--shadow-md);
            padding: 48px;
            text-align: center;
            max-width: 500px;
            margin: 40px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .alert-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }

        .alert-icon.danger {
            background: #FEECEB;
            color: #D32F2F;
        }

        .alert-icon.info {
            background: #E8F4FD;
            color: #0288D1;
        }

        .alert-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--midnight);
        }

        .alert-desc {
            font-size: 15px;
            color: var(--soft-steel);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 24px 0;
            font-size: 13px;
            color: var(--soft-steel);
            font-weight: 500;
            margin-top: 20px;
            border-top: 1px solid rgba(19, 68, 116, 0.08);
        }

        footer strong {
            color: var(--deep-blue);
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .classroom-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                padding: 24px;
            }

            .semester-badge {
                align-self: flex-start;
            }

            .classroom-details h1 {
                font-size: 22px;
            }

            .alert-card {
                padding: 32px 20px;
            }

            .schedule-table th, .schedule-table td {
                padding: 14px 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Top GAMA Branding bar -->
    <header class="gama-logo-bar">
        <div class="brand">
            <i class="fa-solid fa-graduation-cap"></i>
            <h2>G.A.M.A. Solutions</h2>
        </div>
        <div class="time-badge">
            <i class="fa-regular fa-clock"></i>
            <span>Public View</span>
        </div>
    </header>

    <!-- Classroom Header Card -->
    <section class="classroom-card">
        <div class="classroom-info">
            <div class="classroom-icon">
                <i class="fa-solid fa-school"></i>
            </div>
            <div class="classroom-details">
                <h1>{{ $aula->classroom_name }}</h1>
                <div class="classroom-meta">
                    <span class="meta-item">
                        <i class="fa-solid fa-building"></i>
                        {{ $aula->building?->name ?? 'Sin Edificio' }}
                    </span>
                    <span class="meta-item">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ $aula->level?->name ?? 'Sin Nivel' }}
                    </span>
                </div>
            </div>
        </div>

        @if(!$sinSemestre && $semestreVigente)
            <div class="semester-badge">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Semestre Activo: {{ $semestreVigente->name }}</span>
            </div>
        @endif
    </section>

    <!-- Content Schedule or Alerts -->
    @if($sinSemestre)
        <div class="alert-card">
            <div class="alert-icon danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2 class="alert-title">Horario No Disponible</h2>
            <p class="alert-desc">No hay un semestre vigente activo en este momento. El horario público no se puede visualizar.</p>
        </div>
    @elseif($horarios->isEmpty())
        <div class="alert-card">
            <div class="alert-icon info">
                <i class="fa-solid fa-calendar-minus"></i>
            </div>
            <h2 class="alert-title">Sin Clases Asignadas</h2>
            <p class="alert-desc">Esta aula no cuenta con horarios registrados para el semestre vigente <strong>{{ $semestreVigente->name }}</strong>.</p>
        </div>
    @else
        <main class="schedule-card">
            <div class="table-responsive">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Horario</th>
                            <th>Materia</th>
                            <th>Docente</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horarios as $horario)
                            @php
                                // Get the readable weekday name using Enum labels
                                $enumDay = \App\Enums\Schedules\Weekday::tryFrom(strtolower(trim($horario->weekday)));
                                $dayName = $enumDay ? $enumDay->label() : $horario->weekday;
                                
                                // Get teacher profile name
                                $teacherName = 'Sin asignar';
                                if ($horario->teacher) {
                                    $profile = $horario->teacher->getProfileFromSam();
                                    $teacherName = $profile['fullName'];
                                } else {
                                    $teacherName = $horario->teacher_external_id;
                                }

                                // Get teacher initials
                                $initials = '??';
                                if ($teacherName && $teacherName !== 'Sin asignar') {
                                    $words = explode(' ', $teacherName);
                                    if (count($words) >= 2) {
                                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($teacherName, 0, 2));
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <span class="day-badge">
                                        <i class="fa-regular fa-calendar-days"></i>
                                        {{ $dayName }}
                                    </span>
                                </td>
                                <td>
                                    <span class="time-text">
                                        <i class="fa-regular fa-clock" style="color: var(--soft-steel); margin-right: 4px;"></i>
                                        {{ \Carbon\Carbon::parse($horario->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($horario->end_time)->format('H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="subject-text">{{ $horario->subject_name }}</span>
                                </td>
                                <td>
                                    <div class="teacher-info">
                                        <div class="teacher-avatar">
                                            {{ $initials }}
                                        </div>
                                        <span class="teacher-name">{{ $teacherName }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="group-badge">
                                        <i class="fa-solid fa-users" style="font-size: 11px; margin-right: 4px;"></i>
                                        {{ $horario->group_name }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    @endif

    <footer>
        <p>&copy; {{ date('Y') }} <strong>G.A.M.A. SOLUTIONS</strong>. Todos los derechos reservados.</p>
    </footer>
</div>

</body>
</html>
