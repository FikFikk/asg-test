# 🛠️ Laravel Development Tools

## Overview

Halaman development tools ini menyediakan antarmuka web yang mudah digunakan untuk mengelola cache, optimasi, dan debugging aplikasi Laravel Anda.

## Akses Development Tools

**URL**: `/dev/cache`

⚠️ **Penting**: Tools ini hanya tersedia di environment `local` dan `staging` untuk keamanan.

## Fitur Utama

### 🗂️ Cache Management

-   **Clear Application Cache**: Membersihkan semua cache aplikasi
-   **Configuration Cache**: Cache/clear konfigurasi aplikasi
-   **Route Cache**: Cache/clear routing untuk performa lebih baik
-   **View Cache**: Cache/clear compiled views
-   **Event Cache**: Cache/clear event discovery
-   **Optimization**: Cache semua komponen sekaligus

### 🗄️ Database & Storage

-   **Migrations**: Jalankan migrasi database
-   **Fresh Migrate**: Reset database dengan migrasi fresh
-   **Seed Database**: Jalankan database seeders
-   **Storage Link**: Buat symbolic link untuk storage
-   **Queue Management**: Proses dan restart queue workers

### ℹ️ System Information

-   **PHP Information**: Versi, memory limit, konfigurasi
-   **Laravel Information**: Versi, environment, debug mode
-   **Server Information**: OS, web server, hostname
-   **Database Configuration**: Driver, host, database info
-   **Cache & Storage**: Driver configuration
-   **PHP Extensions**: Daftar ekstensi yang terinstall
-   **Application Paths**: Base path, storage, public paths

### 🛣️ Routes Viewer

-   **Route List**: Semua routes yang terdaftar
-   **Search & Filter**: Cari berdasarkan URI, name, atau action
-   **Method Filter**: Filter berdasarkan HTTP method
-   **Route Statistics**: Total routes, GET/POST count
-   **Middleware Info**: Middleware yang digunakan setiap route

### 🔧 Environment Variables

-   **Safe Environment Display**: Tampilkan env vars yang aman
-   **Configuration Status**: Status cache dan optimasi
-   **Instance Configuration**: AWS instance information
-   **Environment File Status**: Status .env dan .env.example

### 📝 Logs Viewer

-   **Real-time Log Viewing**: Lihat log aplikasi secara real-time
-   **Log Level Filtering**: Filter berdasarkan level (error, warning, info, dll)
-   **Auto Refresh**: Refresh otomatis setiap 10 detik
-   **Log Management**: Clear logs, view log file info
-   **Color-coded Levels**: Visual indicator untuk setiap log level

## Quick Actions

### Development Setup

```bash
# Clear all caches (via web interface)
- Clear Application Cache
- Clear Config Cache
- Clear Route Cache
- Clear View Cache
```

### Production Optimization

```bash
# Optimize for production (via web interface)
- Cache Config
- Cache Routes
- Cache Views
- Run Optimize Command
```

## Security Features

### Environment Restrictions

-   Tools hanya aktif di `local` dan `staging` environment
-   Automatic 404 response di production
-   Sensitive data disembunyikan (passwords, API keys)

### Safe Operations

-   Confirmation dialogs untuk operasi destructive
-   Read-only display untuk sensitive information
-   Auto-hide success/error messages

## Navigation

Setiap halaman development tools memiliki navigation bar dengan quick access ke:

-   🗂️ **Cache Management** - Kelola cache dan optimasi
-   ℹ️ **System Info** - Informasi sistem dan aplikasi
-   🛣️ **Routes** - Daftar dan pencarian routes
-   🔧 **Environment** - Environment variables dan konfigurasi
-   📝 **Logs** - Log viewer dan management
-   🏠 **Home** - Kembali ke homepage
-   📊 **Instance Monitor** - AWS instance monitoring

## Penggunaan di Development

### Daily Development Workflow

1. **Start Development**:
    - Clear all caches untuk development yang bersih
    - Check environment variables
2. **During Development**:
    - Monitor logs untuk debugging
    - Check routes saat menambah endpoint baru
    - Clear view cache saat mengubah template
3. **Before Testing**:
    - Run migrations jika ada perubahan database
    - Clear dan rebuild cache
    - Check system info untuk memastikan konfigurasi

### Performance Testing

1. **Optimize Application** untuk simulasi production
2. **Clear Optimization** untuk development normal
3. **Monitor Logs** untuk error atau warning

## Deployment Notes

### Production Deployment

-   Development tools **TIDAK** akan muncul di production
-   Link "Dev Tools" hanya muncul di environment local/staging
-   Routes development otomatis return 404 di production

### Staging Environment

-   Tools tetap tersedia untuk testing dan debugging
-   Gunakan untuk verifikasi optimasi sebelum production
-   Monitor logs untuk issue dalam staging

## Troubleshooting

### Tools Tidak Muncul

-   Pastikan `APP_ENV=local` atau `APP_ENV=staging`
-   Clear config cache: `php artisan config:clear`
-   Check route cache: `php artisan route:clear`

### Permission Errors

-   Pastikan storage dan cache directories writable
-   Check file permissions: `chmod -R 775 storage bootstrap/cache`

### Cache Issues

-   Clear all caches melalui tools
-   Restart web server jika diperlukan
-   Check disk space untuk cache storage

## Best Practices

### Development

-   Clear cache saat switch branch atau pull changes
-   Monitor logs untuk debugging issues
-   Use route viewer untuk verify API endpoints

### Staging

-   Run optimization sebelum testing performance
-   Clear logs sebelum testing untuk clean monitoring
-   Verify environment configuration

### Security

-   Never enable in production
-   Hide sensitive environment variables
-   Use confirmation dialogs untuk destructive operations

## Integration dengan AWS Monitoring

Development tools terintegrasi dengan AWS instance monitoring:

-   Environment configuration menampilkan AWS instance info
-   System information include instance metadata
-   Logs dapat digunakan untuk debug AWS-specific issues

Kombinasi development tools + instance monitoring memberikan complete debugging environment untuk aplikasi Laravel di AWS! 🚀
