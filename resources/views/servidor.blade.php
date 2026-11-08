<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor de servidor</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; margin: 0; padding: 2rem; color: #0f172a; }
        h1 { color: #1e3a8a; margin-bottom: .25rem; }
        .sub { color: #64748b; margin-top: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
        .card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .label { font-size: .85rem; color: #64748b; }
        .value { font-size: 2rem; font-weight: 700; margin: .25rem 0; color: #1e3a8a; }
        .meta { margin-top: 1.5rem; color: #64748b; font-size: .85rem; }
    </style>
</head>
<body>
    <h1>Monitor de servidor</h1>
    <p class="sub">{{ $m['hostname'] }} · {{ $m['os'] }} · PHP {{ $m['php'] }}</p>

    <div class="grid">
        <div class="card">
            <div class="label">CPU</div>
            <div class="value" id="cpu">{{ $m['cpu_percent'] }} %</div>
        </div>
        <div class="card">
            <div class="label">Memoria</div>
            <div class="value" id="mem">{{ $m['memory']['percent'] }} %</div>
            <div class="label" id="mem-detalle">{{ $m['memory']['used_mb'] }} / {{ $m['memory']['total_mb'] }} MB</div>
        </div>
        <div class="card">
            <div class="label">Disco</div>
            <div class="value" id="disco">{{ $m['disk']['percent'] }} %</div>
            <div class="label" id="disco-detalle">{{ $m['disk']['free_gb'] }} GB libres de {{ $m['disk']['total_gb'] }} GB</div>
        </div>
        <div class="card">
            <div class="label">Encendido hace</div>
            <div class="value" id="uptime">{{ $m['uptime_hours'] }} h</div>
        </div>
    </div>

    <p class="meta">Última actualización: <span id="ts">{{ $m['timestamp'] }}</span></p>

    <script>
    async function actualizar() {
        try {
            const res = await fetch('/api/metrics');
            const m = await res.json();

            document.getElementById('cpu').textContent = m.cpu_percent + ' %';
            document.getElementById('mem').textContent = m.memory.percent + ' %';
            document.getElementById('mem-detalle').textContent = m.memory.used_mb + ' / ' + m.memory.total_mb + ' MB';
            document.getElementById('disco').textContent = m.disk.percent + ' %';
            document.getElementById('disco-detalle').textContent = m.disk.free_gb + ' GB libres de ' + m.disk.total_gb + ' GB';
            document.getElementById('uptime').textContent = m.uptime_hours + ' h';
            document.getElementById('ts').textContent = m.timestamp;
        } catch (e) {
            console.error('No se pudo actualizar:', e);
        }
    }

    setInterval(actualizar, 5000); // cada 5 segundos
    </script>
    
</body>
</html>