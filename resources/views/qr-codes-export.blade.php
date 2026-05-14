<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Codes Export</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .page-break { page-break-after: always; }
        .qr-page { text-align: center; padding: 40px 0; }
        .qr-page h2 { margin-bottom: 10px; color: #333; }
        .qr-page p { margin: 5px 0; color: #666; font-size: 14px; }
        .qr-page img { margin: 20px 0; max-width: 300px; }
    </style>
</head>
<body>
    @foreach($classrooms as $index => $classroom)
        <div class="qr-page">
            <h2>{{ $classroom['classroomName'] }}</h2>
            <p>{{ $classroom['buildingName'] }}</p>
            <img src="{{ $classroom['qrPath'] }}" alt="QR Code">
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
