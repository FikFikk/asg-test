<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# AWS Instance Monitoring untuk Laravel

## Overview

Aplikasi Laravel ini dilengkapi dengan real-time monitoring untuk AWS EC2 instances yang menjalankan Laravel aplikasi dengan Load Balancer dan Auto Scaling Group.

## Fitur AWS Monitoring

-   📊 **Real-time CPU monitoring** dengan grafik interaktif
-   🏷️ **Instance identification** (ID, Name, Type, AZ, IP)
-   🔥 **CPU stress testing** untuk validasi auto scaling
-   📈 **Live charts** dengan Chart.js
-   🔄 **Auto refresh** setiap 5 detik
-   🎨 **Responsive design** dengan Tailwind CSS

## Cara Kerja

1. **Instance Info**: Mengambil metadata EC2 dari `http://169.254.169.254/latest/meta-data/`
2. **CPU Monitoring**: Menggunakan `sys_getloadavg()` untuk Linux atau `wmic` untuk Windows
3. **Stress Testing**: Simulasi beban CPU untuk trigger auto scaling
4. **Real-time Updates**: AJAX calls untuk update data tanpa refresh halaman

## Setup untuk AWS Deployment

### 1. Environment Variables

Tambahkan ke `.env` file di setiap instance:

```env
INSTANCE_NAME="Web-Server-1"
# atau gunakan script untuk auto-generate nama berdasarkan AZ dan Instance ID
```

### 2. User Data Script

Gunakan `deployment/user-data.sh` saat launch instance untuk:

-   Install dependencies (Apache, PHP, Composer)
-   Clone dan setup Laravel aplikasi
-   Set instance-specific environment variables
-   Configure CloudWatch monitoring

### 3. Auto Scaling Group

Gunakan `deployment/terraform-asg.tf` untuk setup:

-   Launch Template dengan user data
-   Auto Scaling Group (min: 2, max: 6)
-   Application Load Balancer
-   CloudWatch alarms untuk CPU-based scaling
-   Security groups dan IAM roles

## Routes untuk Monitoring

-   `GET /` - Welcome page dengan link ke monitoring
-   `GET /instance` - Halaman monitoring utama
-   `GET /instance/cpu` - API endpoint untuk current CPU usage
-   `POST /instance/stress-cpu` - API endpoint untuk CPU stress test

## Testing Auto Scaling

1. Akses halaman monitoring: `http://your-load-balancer-dns/instance`
2. Jalankan stress test dengan intensitas tinggi (80-90%)
3. Monitor CPU usage chart
4. Cek AWS Console untuk scaling activity
5. Refresh halaman untuk melihat instance yang berbeda (load balancer distribution)

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
