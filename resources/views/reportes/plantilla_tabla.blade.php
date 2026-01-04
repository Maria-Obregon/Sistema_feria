{{-- resources/views/reportes/plantilla_tabla.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ $titulo ?? 'Reporte' }}</title>
  <style>
    @page { margin: 1.5cm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size: 11pt; }
    h1 { font-size: 15pt; margin: 0 0 10px 0; }
    .meta { font-size: 9.5pt; color:#555; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
    th { background: #f3f4f6; text-align: left; font-size: 10pt; }
    td { font-size: 10pt; }
    .right { text-align:right; }
    .center { text-align:center; }
    .muted { color:#6b7280; }
    .pie { margin-top: 14px; font-size: 9.5pt; color:#555; }
    .badge { display:inline-block; padding: 2px 6px; border-radius: 10px; font-size: 9pt; }
    .b-ok { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .b-pend { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
  </style>
</head>
<body>
  <h1>{{ $titulo ?? '' }}</h1>

  <div class="meta">
    Generado: {{ now()->format('d/m/Y H:i') }}
    @if(!empty($subtitulo))
      <span class="muted"> — {{ $subtitulo }}</span>
    @endif
  </div>

  <table>
    <thead>
      <tr>
        @foreach(($headers ?? []) as $h)
          <th>{!! e($h) !!}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse(($rows ?? []) as $row)
        <tr>
          @foreach($row as $cell)
            <td>{!! $cell !!}</td>
          @endforeach
        </tr>
      @empty
        <tr>
          <td colspan="{{ count($headers ?? []) }}" class="center muted">Sin datos</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  @if(!empty($pie))
    <div class="pie">{{ $pie }}</div>
  @endif
</body>
</html>
