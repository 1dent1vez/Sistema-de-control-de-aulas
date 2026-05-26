{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Vista pública simple del horario de un aula consultada por código QR (standalone, directa y sin redirecciones).
 * @autor          Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 * @autorizador    Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 * @prueba         Diego Miguel Hernandez Fabela <correo@dominio.com>
 * @mantenimiento  Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 * @version        1.1.0
 * @creado         2026-05-26
 * @modificado     2026-05-26
 * @cambios        2026-05-26 - Actualización de estilos a la vista de horario con gradientes y visualización directa
 */
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario - {{ $aula->classroom_name ?? 'Aula' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #134474 0%, #1E5A8A 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #134474; color: white; padding: 15px; text-align: left; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9ff; }
        .alert { padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-primary { background: #1E5A8A; color: white; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
        @media (max-width: 600px) {
            .header h1 { font-size: 22px; }
            th, td { padding: 10px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $aula->classroom_name ?? 'Aula' }}</h1>
            <p>Horario del Semestre Vigente</p>
        </div>

        <div class="content">
            @if(!$semestreVigente)
                <div class="alert alert-warning">
                    <strong>No hay semestre vigente.</strong><br>
                    El horario no está disponible actualmente.
                </div>
            @elseif($horarios->isEmpty())
                <div class="alert alert-info">
                    <strong>Sin horarios asignados.</strong><br>
                    Esta aula no tiene horarios en el semestre vigente.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Docente</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horarios as $horario)
                        @php
                            $enumDay = \App\Enums\Schedules\Weekday::tryFrom(strtolower(trim($horario->weekday)));
                            $dayName = $enumDay ? $enumDay->label() : $horario->weekday;
                            
                            $teacherName = 'Sin asignar';
                            if ($horario->teacher) {
                                $profile = $horario->teacher->getProfileFromSam();
                                $teacherName = $profile['fullName'];
                            } else {
                                $teacherName = $horario->teacher_external_id ?? 'Sin asignar';
                            }
                        @endphp
                        <tr>
                            <td><span class="badge badge-primary">{{ $dayName }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($horario->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($horario->end_time)->format('H:i') }}</td>
                            <td>{{ $teacherName }}</td>
                            <td>{{ $horario->subject_name ?? '-' }}</td>
                            <td>{{ $horario->group_name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="footer">
                    Semestre: <strong>{{ $semestreVigente->name }}</strong> |
                    Vigente del {{ $semestreVigente->start_date->format('d/m/Y') }} al {{ $semestreVigente->end_date->format('d/m/Y') }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
