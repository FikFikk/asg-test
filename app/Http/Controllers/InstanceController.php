<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstanceController extends Controller
{
    public function index()
    {
        $instanceInfo = $this->getInstanceInfo();
        $cpuUsage = $this->getCPUUsage();

        return view('instance-info', compact('instanceInfo', 'cpuUsage'));
    }

    private function getInstanceInfo()
    {
        // Cek apakah running di AWS EC2
        $isAwsInstance = $this->isRunningOnAws();

        if ($isAwsInstance) {
            try {
                // Mendapatkan Instance ID
                $instanceId = $this->getMetadata('instance-id');

                // Mendapatkan Instance Type
                $instanceType = $this->getMetadata('instance-type');

                // Mendapatkan Availability Zone
                $availabilityZone = $this->getMetadata('placement/availability-zone');

                // Mendapatkan Local IP
                $localIp = $this->getMetadata('local-ipv4');

                // Mendapatkan Public IP (jika ada)
                $publicIp = $this->getMetadata('public-ipv4');

                // Mendapatkan Instance Name dari berbagai sumber
                $instanceName = $this->getInstanceName($instanceId, $availabilityZone);

                return [
                    'instance_id' => $instanceId,
                    'instance_name' => $instanceName,
                    'instance_type' => $instanceType,
                    'availability_zone' => $availabilityZone,
                    'local_ip' => $localIp,
                    'public_ip' => $publicIp ?: 'N/A',
                    'hostname' => gethostname(),
                    'region' => substr($availabilityZone, 0, -1), // Remove last character (zone letter)
                ];
            } catch (\Exception $e) {
                return $this->getLocalInstanceInfo();
            }
        } else {
            return $this->getLocalInstanceInfo();
        }
    }

    private function getMetadata($path)
    {
        try {
            $response = Http::timeout(2)->get("http://169.254.169.254/latest/meta-data/{$path}");
            return $response->successful() ? $response->body() : 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function isRunningOnAws()
    {
        try {
            // Coba akses AWS metadata service
            $response = Http::timeout(2)->get("http://169.254.169.254/latest/meta-data/instance-id");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getLocalInstanceInfo()
    {
        return [
            'instance_id' => 'localhost-' . substr(md5(gethostname()), 0, 8),
            'instance_name' => 'Local Development Server',
            'instance_type' => 'development',
            'availability_zone' => 'local-zone',
            'local_ip' => $this->getLocalIpAddress(),
            'public_ip' => 'N/A',
            'hostname' => gethostname(),
            'region' => 'local',
        ];
    }

    private function getLocalIpAddress()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('ipconfig | findstr /R /C:"IPv4 Address"');
                if ($output && preg_match('/(\d+\.\d+\.\d+\.\d+)/', $output, $matches)) {
                    return $matches[1];
                }
            } else {
                $output = shell_exec("hostname -I | awk '{print $1}'");
                if ($output) {
                    return trim($output);
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }
        return '127.0.0.1';
    }

    private function getInstanceName($instanceId, $availabilityZone = null)
    {
        // Priority order untuk mendapatkan instance name:
        // 1. Environment variable INSTANCE_NAME
        // 2. Auto-generated dari AZ dan Instance ID
        // 3. Hostname
        // 4. Default fallback

        // 1. Cek environment variable
        $envName = env('INSTANCE_NAME');
        if ($envName && $envName !== '') {
            return $envName;
        }

        // 2. Auto-generate dari AZ dan Instance ID
        if ($availabilityZone && $instanceId !== 'N/A') {
            $shortInstanceId = substr($instanceId, 2, 8); // Ambil 8 karakter setelah 'i-'
            return "Web-Server-{$availabilityZone}-{$shortInstanceId}";
        }

        // 3. Gunakan hostname jika tersedia
        $hostname = gethostname();
        if ($hostname && $hostname !== false) {
            return "Server-{$hostname}";
        }

        // 4. Fallback default
        return $instanceId !== 'N/A' ? "Instance-{$instanceId}" : 'Unknown-Server';
    }

    private function getCPUUsage()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Untuk Windows
                $cmd = 'wmic cpu get loadpercentage /value';
                $output = shell_exec($cmd);
                preg_match('/LoadPercentage=(\d+)/', $output, $matches);
                return isset($matches[1]) ? (int)$matches[1] : 0;
            } else {
                // Untuk Linux
                $load = sys_getloadavg();
                return round($load[0] * 100 / 4, 2); // Asumsi 4 core CPU
            }
        } catch (\Exception $e) {
            return rand(10, 80); // Random untuk demo
        }
    }

    public function testCpu(Request $request)
    {
        $duration = $request->input('duration', 10); // Default 10 detik
        $intensity = $request->input('intensity', 50); // Default 50% intensity

        return response()->json([
            'status' => 'started',
            'message' => 'CPU stress test dimulai',
            'duration' => $duration,
            'intensity' => $intensity
        ]);
    }

    public function stressCpu(Request $request)
    {
        $duration = (int) $request->input('duration', 10);
        $intensity = (int) $request->input('intensity', 50);

        ignore_user_abort(true);
        set_time_limit(0);

        $startTime = time();
        $endTime = $startTime + $duration;

        while (time() < $endTime) {
            // Simulasi beban CPU
            for ($i = 0; $i < $intensity * 1000; $i++) {
                $temp = sqrt(rand(1, 1000000));
            }

            // Jeda singkat untuk mengontrol intensitas
            usleep((100 - $intensity) * 100);
        }

        return response()->json([
            'status' => 'completed',
            'message' => 'CPU stress test selesai',
            'duration' => $duration
        ]);
    }

    public function getCurrentCpu()
    {
        return response()->json([
            'cpu_usage' => $this->getCPUUsage(),
            'timestamp' => now()->toISOString()
        ]);
    }

    public function getInstanceInfoApi()
    {
        return response()->json([
            'instance_info' => $this->getInstanceInfo(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
