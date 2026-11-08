<?php

namespace App\Services;

class ServerMetrics
{
    public function snapshot(): array
    {
        $esWindows = PHP_OS_FAMILY === 'Windows';
        $data = $esWindows ? $this->windows() : $this->linux();

        $unidad = $esWindows ? 'C:' : '/';
        $discoTotal = disk_total_space($unidad);
        $discoLibre = disk_free_space($unidad);

        return [
            'hostname'    => gethostname(),
            'os'          => $data['os'],
            'php'         => PHP_VERSION,
            'cpu_percent' => round($data['cpu'], 1),
            'memory' => [
                'total_mb' => round($data['mem_total_kb'] / 1024),
                'used_mb'  => round(($data['mem_total_kb'] - $data['mem_free_kb']) / 1024),
                'percent'  => round(100 * (1 - $data['mem_free_kb'] / $data['mem_total_kb']), 1),
            ],
            'disk' => [
                'total_gb' => round($discoTotal / 1024 ** 3, 1),
                'free_gb'  => round($discoLibre / 1024 ** 3, 1),
                'percent'  => round(100 * (1 - $discoLibre / $discoTotal), 1),
            ],
            'uptime_hours' => round($data['uptime_s'] / 3600, 1),
            'timestamp'    => now()->toIso8601String(),
        ];
    }

    // Windows: PowerShell + WMI, devuelve JSON
    private function windows(): array
    {
        $ps = '$os = Get-CimInstance Win32_OperatingSystem; '
            . '$cpu = (Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average; '
            . '[PSCustomObject]@{ os = $os.Caption; cpu = $cpu; '
            . 'mem_total_kb = $os.TotalVisibleMemorySize; mem_free_kb = $os.FreePhysicalMemory; '
            . 'uptime_s = ((Get-Date) - $os.LastBootUpTime).TotalSeconds } | ConvertTo-Json';

        $json = shell_exec('powershell -NoProfile -Command "' . $ps . '"');

        return json_decode($json, true);
    }

    // Linux (Docker/Sail): lee los archivos de /proc
    private function linux(): array
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $libre);

        $nucleos = (int) trim(shell_exec('nproc')) ?: 1;

        return [
            'os'           => php_uname('s') . ' ' . php_uname('r'),
            'cpu'          => min(100, sys_getloadavg()[0] / $nucleos * 100), // aproximado
            'mem_total_kb' => (int) $total[1],
            'mem_free_kb'  => (int) $libre[1],
            'uptime_s'     => (float) explode(' ', file_get_contents('/proc/uptime'))[0],
        ];
    }
}