# Miss Whitney · Laravel — Instalación

## Requisitos
- PHP 8.2+, Composer, XAMPP con MySQL y Apache

## Pasos

### 1. Coloca en XAMPP
```
C:\xampp\htdocs\laravel\mw_laravelWeb\
```

### 2. Instala dependencias (incluye dompdf)
```powershell
composer update
```

### 3. Crea la BD en phpMyAdmin
Base de datos `misswhitney` (utf8mb4_unicode_ci)

### 4. Ejecuta migraciones
```powershell
php artisan migrate
```

### 5. Enlaza el storage
```powershell
php artisan storage:link
```

### 6. Limpia caché
```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## URLs
| Página | URL |
|---|---|
| Inicio | http://localhost/laravel/mw_laravelWeb/public/ |
| Carta | http://localhost/laravel/mw_laravelWeb/public/carta |
| Solicitar factura | http://localhost/laravel/mw_laravelWeb/public/solicitar-factura |
| Panel admin | http://localhost/laravel/mw_laravelWeb/public/admin |
