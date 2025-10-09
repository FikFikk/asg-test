<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // Force check AWS environment dengan multiple methods
        $isAwsInstance = $this->isRunningOnAws();
        
        if ($isAwsInstance) {
            try {
                // Mendapatkan Instance ID dengan multiple fallbacks
                $instanceId = $this->getMetadataWithFallback('instance-id');
                
                if ($instanceId === 'N/A') {
                    // Fallback methods untuk mendapatkan instance ID
                    $instanceId = $this->getInstanceIdFromSystem();
                }
                
                // Mendapatkan Instance Type
                $instanceType = $this->getMetadataWithFallback('instance-type');
                
                // Mendapatkan Availability Zone
                $availabilityZone = $this->getMetadataWithFallback('placement/availability-zone');
                
                // Mendapatkan Local IP
                $localIp = $this->getMetadataWithFallback('local-ipv4');
                
                // Mendapatkan Public IP (jika ada)
                $publicIp = $this->getMetadataWithFallback('public-ipv4');
                
                // Mendapatkan Instance Name dari berbagai sumber
                $instanceName = $this->getInstanceName($instanceId, $availabilityZone);
                
                return [
                    'instance_id' => $instanceId,
                    'instance_name' => $instanceName,
                    'instance_type' => $instanceType,
                    'availability_zone' => $availabilityZone,
                    'local_ip' => $localIp !== 'N/A' ? $localIp : $this->getLocalIpAddress(),
                    'public_ip' => $publicIp ?: 'N/A',
                    'hostname' => gethostname(),
                    'region' => $availabilityZone !== 'N/A' ? substr($availabilityZone, 0, -1) : 'N/A',
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to get AWS instance info, falling back to local', ['error' => $e->getMessage()]);
                return $this->getLocalInstanceInfo();
            }
        } else {
            return $this->getLocalInstanceInfo();
        }
    }
    
    private function getMetadataWithFallback($path)
    {
        // Try the standard metadata endpoint first
        $value = $this->getMetadata($path);
        
        if ($value !== 'N/A') {
            return $value;
        }
        
        // Fallback methods for specific metadata
        switch ($path) {
            case 'instance-id':
                return $this->getInstanceIdFromSystem();
            case 'local-ipv4':
                return $this->getLocalIpAddress();
            case 'instance-type':
                return $this->getInstanceTypeFromSystem();
            case 'placement/availability-zone':
                return $this->getAZFromSystem();
            default:
                return 'N/A';
        }
    }
    
    private function getInstanceIdFromSystem()
    {
        try {
            // Method 1: Check environment variables
            $instanceId = getenv('AWS_INSTANCE_ID') ?: env('AWS_INSTANCE_ID');
            if ($instanceId) {
                return $instanceId;
            }
            
            // Method 2: Try to read from cloud-init or system files
            $possibleFiles = [
                '/var/lib/cloud/data/instance-id',
                '/opt/aws/bin/ec2-metadata --instance-id',
                '/sys/devices/virtual/dmi/id/board_asset_tag'
            ];
            
            foreach ($possibleFiles as $file) {
                if (strpos($file, 'ec2-metadata') !== false) {
                    $output = @shell_exec($file . ' 2>/dev/null');
                    if ($output && preg_match('/i-[a-f0-9]+/', $output, $matches)) {
                        return $matches[0];
                    }
                } elseif (file_exists($file)) {
                    $content = @file_get_contents($file);
                    if ($content && preg_match('/i-[a-f0-9]+/', $content, $matches)) {
                        return $matches[0];
                    }
                }
            }
            
            // Method 3: Generate a consistent fake instance ID based on system info
            $hostname = gethostname();
            $machineId = @file_get_contents('/etc/machine-id') ?: $hostname;
            return 'i-' . substr(md5($machineId), 0, 17);
            
        } catch (\Exception $e) {
            return 'i-' . substr(md5(gethostname() . time()), 0, 17);
        }
    }
    
    private function getInstanceTypeFromSystem()
    {
        try {
            // Check environment variable
            $type = getenv('AWS_INSTANCE_TYPE') ?: env('AWS_INSTANCE_TYPE');
            if ($type) {
                return $type;
            }
            
            // Try to get from system info
            $memInfo = @file_get_contents('/proc/meminfo');
            if ($memInfo && preg_match('/MemTotal:\s+(\d+)\s+kB/', $memInfo, $matches)) {
                $memMB = intval($matches[1]) / 1024;
                
                // Rough mapping based on memory
                if ($memMB < 1500) {
                    return 't2.micro';
                } elseif ($memMB < 3000) {
                    return 't2.small';
                } elseif ($memMB < 6000) {
                    return 't2.medium';
                } else {
                    return 't2.large';
                }
            }
            
            return 't2.micro'; // Default
            
        } catch (\Exception $e) {
            return 't2.micro';
        }
    }
    
    private function getAZFromSystem()
    {
        try {
            // Check environment variable
            $az = getenv('AWS_AVAILABILITY_ZONE') ?: env('AWS_AVAILABILITY_ZONE');
            if ($az) {
                return $az;
            }
            
            $region = getenv('AWS_REGION') ?: getenv('AWS_DEFAULT_REGION') ?: env('AWS_REGION');
            if ($region) {
                return $region . 'a'; // Default to 'a' zone
            }
            
            return 'ap-southeast-1a'; // Default for your region
            
        } catch (\Exception $e) {
            return 'ap-southeast-1a';
        }
    }    private function getMetadata($path)
    {
        try {
            // Method 1: Using file_get_contents (sometimes more reliable)
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'method' => 'GET',
                    'header' => [
                        "User-Agent: Laravel-Instance-Monitor\r\n",
                        "X-aws-ec2-metadata-token-ttl-seconds: 21600\r\n"
                    ]
                ]
            ]);
            
            $url = "http://169.254.169.254/latest/meta-data/{$path}";
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false && $response !== '') {
                return trim($response);
            }
            
            // Method 2: Fallback to Http client
            $response = Http::timeout(3)
                ->withHeaders([
                    'User-Agent' => 'Laravel-Instance-Monitor',
                    'X-aws-ec2-metadata-token-ttl-seconds' => '21600'
                ])
                ->get($url);
                
            if ($response->successful() && $response->body() !== '') {
                return trim($response->body());
            }
            
        } catch (\Exception $e) {
            Log::warning("Failed to get AWS metadata for path: {$path}", ['error' => $e->getMessage()]);
        }
        
        return 'N/A';
    }

    private function isRunningOnAws()
    {
        // Multiple ways to detect if we're running on AWS
        
        // Method 1: Check if we can reach AWS metadata service
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 1,
                    'method' => 'GET',
                    'header' => "User-Agent: Laravel-Instance-Monitor\r\n"
                ]
            ]);
            
            $response = @file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, $context);
            if ($response !== false && strpos($response, 'i-') === 0) {
                return true;
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        // Method 2: Check for AWS-specific environment indicators
        if (getenv('AWS_EXECUTION_ENV') || getenv('AWS_REGION') || getenv('AWS_DEFAULT_REGION')) {
            return true;
        }
        
        // Method 3: Check for EC2 instance metadata in system info
        if (file_exists('/sys/hypervisor/uuid')) {
            $uuid = @file_get_contents('/sys/hypervisor/uuid');
            if ($uuid && strpos(strtolower($uuid), 'ec2') === 0) {
                return true;
            }
        }
        
        // Method 4: Check for AWS instance store
        if (file_exists('/dev/nvme0n1') || file_exists('/dev/xvda1')) {
            return true;
        }
        
        // Method 5: Check hostname pattern (AWS instances usually have specific patterns)
        $hostname = gethostname();
        if ($hostname && (
            strpos($hostname, 'ip-') === 0 || 
            strpos($hostname, 'ec2-') !== false ||
            preg_match('/^ip-\d{1,3}-\d{1,3}-\d{1,3}-\d{1,3}/', $hostname)
        )) {
            return true;
        }
        
        return false;
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
        // 2. Generate "Server X" berdasarkan instance ID
        // 3. Auto-generated dari AZ dan Instance ID
        // 4. Hostname
        // 5. Default fallback
        
        // 1. Cek environment variable
        $envName = env('INSTANCE_NAME');
        if ($envName && $envName !== '') {
            return $envName;
        }
        
        // 2. Generate "Server X" berdasarkan instance ID untuk AWS
        if ($instanceId !== 'N/A' && strpos($instanceId, 'i-') === 0) {
            // Ambil bagian hex dari instance ID dan convert ke angka
            $hexPart = substr($instanceId, 2); // Remove 'i-' prefix
            $numericValue = hexdec(substr($hexPart, -4)); // Ambil 4 karakter terakhir dan convert ke decimal
            $serverNumber = ($numericValue % 10) + 1; // Generate 1-10
            return "Server {$serverNumber}";
        }
        
        // 3. Auto-generate dari AZ dan Instance ID untuk format yang konsisten
        if ($availabilityZone && $instanceId !== 'N/A') {
            $shortInstanceId = substr(str_replace('i-', '', $instanceId), 0, 8);
            return "Web-Server-{$availabilityZone}-{$shortInstanceId}";
        }
        
        // 4. Gunakan hostname jika tersedia
        $hostname = gethostname();
        if ($hostname && $hostname !== false) {
            // Jika hostname mengandung pattern AWS, extract server number
            if (preg_match('/ip-(\d+)-(\d+)-(\d+)-(\d+)/', $hostname, $matches)) {
                $serverNumber = (intval($matches[4]) % 10) + 1;
                return "Server {$serverNumber}";
            }
            return "Server-{$hostname}";
        }
        
        // 5. Fallback default
        return $instanceId !== 'N/A' ? "Instance-{$instanceId}" : 'Unknown-Server';
    }    private function getCPUUsage()
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
    
    public function debugInfo()
    {
        $debugInfo = [
            'is_aws_detected' => $this->isRunningOnAws(),
            'hostname' => gethostname(),
            'server_vars' => [
                'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'N/A',
                'SERVER_ADDR' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            ],
            'environment_vars' => [
                'AWS_REGION' => getenv('AWS_REGION') ?: 'Not set',
                'AWS_EXECUTION_ENV' => getenv('AWS_EXECUTION_ENV') ?: 'Not set',
                'INSTANCE_NAME' => env('INSTANCE_NAME') ?: 'Not set',
                'AWS_INSTANCE_ID' => env('AWS_INSTANCE_ID') ?: 'Not set',
            ],
            'system_checks' => [
                'hypervisor_uuid_exists' => file_exists('/sys/hypervisor/uuid'),
                'nvme_exists' => file_exists('/dev/nvme0n1'),
                'xvda_exists' => file_exists('/dev/xvda1'),
                'machine_id_exists' => file_exists('/etc/machine-id'),
            ],
            'metadata_attempts' => [
                'instance_id' => $this->getMetadata('instance-id'),
                'instance_type' => $this->getMetadata('instance-type'),
                'local_ipv4' => $this->getMetadata('local-ipv4'),
                'availability_zone' => $this->getMetadata('placement/availability-zone'),
            ],
            'final_instance_info' => $this->getInstanceInfo(),
        ];
        
        return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
    }
}
