$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$projectRoot = $PSScriptRoot
$archivePath = Join-Path $projectRoot 'ecommerce-cpanel-deploy.zip'
$archive = [System.IO.Compression.ZipFile]::Open($archivePath, [System.IO.Compression.ZipArchiveMode]::Create)
function Add-TextEntry($name, $content) {
    $entry = $archive.CreateEntry('cpanel-deploy/' + $name)
    $writer = [System.IO.StreamWriter]::new($entry.Open(), [System.Text.UTF8Encoding]::new($false))
    try { $writer.Write($content) } finally { $writer.Dispose() }
}
try {
    $folders = @('app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor')
    foreach ($folder in $folders) {
        $files = Get-ChildItem -LiteralPath (Join-Path $projectRoot $folder) -File -Recurse -Force
        foreach ($file in $files) {
            $relative = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
            if ($relative -match '^public/storage/' -or $relative -match '^bootstrap/cache/(?!\.gitignore$)' -or $relative -match '\.(sqlite|sql)$' -or $relative -eq 'public/error_log' -or $relative -eq 'public/hot') { continue }
            [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $file.FullName, 'cpanel-deploy/' + $relative, [System.IO.Compression.CompressionLevel]::Optimal)
        }
    }
    foreach ($file in Get-ChildItem -LiteralPath (Join-Path $projectRoot 'storage/app/public') -File -Recurse -Force) {
        $relative = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
        [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $file.FullName, 'cpanel-deploy/' + $relative, [System.IO.Compression.CompressionLevel]::Optimal)
    }
    foreach ($name in @('artisan', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'vite.config.js', '.env.production.example')) {
        [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, (Join-Path $projectRoot $name), 'cpanel-deploy/' + $name)
    }
    foreach ($directory in @('storage/app/private', 'storage/logs', 'storage/fonts', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache')) {
        [void]$archive.CreateEntry('cpanel-deploy/' + $directory + '/')
    }
    $keyLine = Get-Content -LiteralPath (Join-Path $projectRoot '.env') | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
    if (-not $keyLine -or $keyLine -eq 'APP_KEY=') { throw 'Existing application key missing.' }
    $productionEnv = Get-Content -LiteralPath (Join-Path $projectRoot '.env.production.example') -Raw
    $productionEnv = $productionEnv -replace '(?m)^APP_KEY=.*$', $keyLine
    Add-TextEntry '.env' $productionEnv
    Add-TextEntry 'CPANEL-INSTRUCTIONS-BN.txt' @'
1. PHP 8.2+ ebong dom, gd, mbstring, pdo_mysql, fileinfo, openssl, xml, curl extensions enable korun.
2. ZIP cPanel Home Directory-te extract korun. App public_html-er baire cpanel-deploy folder-e rakhun.
3. Domain document root /home/CPANEL_USERNAME/cpanel-deploy/public set korun.
4. Nijer exported database import korun. ZIP-e database export nei.
5. cpanel-deploy/.env-e APP_URL, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD update korun.
6. Existing APP_KEY preserve kora hoyeche. Imported encrypted settings-er jonno APP_KEY change korben na. .env private rakhun.
7. storage ebong bootstrap/cache PHP-r jonno writable korun.
8. Terminal-e cpanel-deploy folder theke: php artisan optimize:clear
   Tarpor: php artisan storage:link
9. Terminal na thakle routes/web.php-er commented /cmd route ebong proyojoniyo commands uncomment kore super admin login-er por /cmd visit korun. Sheshe abar comment korun.
10. Nijer imported admin account diye /admin/login-e login korun. deploy.php ba seeding chalano lagbe na.
11. Document root change na hole public folder-er contents public_html-e copy korun; index.php-er vendor/autoload.php, bootstrap/app.php ebong maintenance path app-er absolute path-e update korun. public_html/storage app-er storage/app/public-e link korte hosting support-er sahajjo nin.
12. vendor, production build ebong uploaded images included. node_modules, test data, logs, sessions, cache ebong local database credentials excluded.
'@
} finally { $archive.Dispose() }
$check = [System.IO.Compression.ZipFile]::OpenRead($archivePath)
try {
    foreach ($required in @('vendor/autoload.php', 'public/build/manifest.json', 'public/index.php', 'routes/web.php', '.env', 'CPANEL-INSTRUCTIONS-BN.txt')) {
        if (-not $check.GetEntry('cpanel-deploy/' + $required)) { throw "Missing package file: $required" }
    }
    $bad = @($check.Entries | Where-Object { $_.FullName -match '/node_modules/|\.(sql|sqlite)$|/storage/framework/sessions/.+|/public/storage/.+|/storage/logs/.+' })
    if ($bad.Count) { throw 'Unexpected runtime files in package.' }
    [pscustomobject]@{ Path = $archivePath; Entries = $check.Entries.Count; SizeMB = [math]::Round((Get-Item -LiteralPath $archivePath).Length / 1MB, 2); Verified = $true }
} finally { $check.Dispose() }
