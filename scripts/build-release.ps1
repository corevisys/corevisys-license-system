[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [string]$OutputDir = "$env:USERPROFILE\corevisys-release"
)

$ErrorActionPreference = "Stop"
$ProjectRoot = (Resolve-Path "$PSScriptRoot\..").Path

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " CoreVisys License Server - Production Release Builder    " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "Project Root : $ProjectRoot"
Write-Host "Output Dir   : $OutputDir"

# 1. Prerequisite check
Write-Host "`n[1/8] Verifying build tools..." -ForegroundColor Yellow
foreach ($tool in @('git','composer','npm')) {
    if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) { throw "$tool not found in PATH." }
}
Write-Host "  git, composer, npm -- OK" -ForegroundColor Green

# 2. Build frontend assets in the SOURCE repo (not staging)
Write-Host "`n[2/8] Building production frontend assets (npm run build)..." -ForegroundColor Yellow
Push-Location $ProjectRoot
try {
    & npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed (exit $LASTEXITCODE)" }
} finally { Pop-Location }

# 3. Create staging directory via git archive (tracked files only)
$Timestamp   = Get-Date -Format "yyyyMMdd-HHmmss"
$StagingRoot = Join-Path $env:TEMP "corevisys-staging-$Timestamp"
$ZipName     = "corevisys-release-$Timestamp.zip"
if (-not (Test-Path $OutputDir)) { New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null }
$FinalZip    = Join-Path $OutputDir $ZipName

Write-Host "`n[3/8] Exporting tracked files via git archive..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $StagingRoot -Force | Out-Null
Push-Location $ProjectRoot
try {
    $TarFile = Join-Path $env:TEMP "corevisys-git-$Timestamp.tar"
    & git archive --format=tar HEAD -o $TarFile
    if ($LASTEXITCODE -ne 0) { throw "git archive failed (exit $LASTEXITCODE)" }
    & tar -xf $TarFile -C $StagingRoot
    Remove-Item -Force $TarFile
} finally { Pop-Location }

# 4. Copy built assets into staging
Write-Host "`n[4/8] Copying public/build into staging..." -ForegroundColor Yellow
$SrcBuild = Join-Path $ProjectRoot "public\build"
$DstBuild = Join-Path $StagingRoot "public\build"
if (-not (Test-Path $SrcBuild)) { throw "public/build not found - did npm run build succeed?" }
if (Test-Path $DstBuild) { Remove-Item -Recurse -Force $DstBuild }
Copy-Item -Recurse -Path $SrcBuild -Destination $DstBuild
Write-Host "  public/build copied." -ForegroundColor Green

# 5. Ship installer-bootstrap .env so /install works on fresh deploy
#    SESSION_DRIVER=file + CACHE_STORE=file lets the wizard boot without a DB.
#    The /install wizard overwrites this .env with full production values.
#    We also inject a unique APP_KEY so Laravel can boot and render the CSRF form.
Write-Host "`n[5/8] Preparing installer-bootstrap .env with unique APP_KEY..." -ForegroundColor Yellow
$EnvBootstrap = Join-Path $ProjectRoot "deploy\.env.install-bootstrap"
if (-not (Test-Path $EnvBootstrap)) { throw "deploy/.env.install-bootstrap not found in project root." }
$EnvContent = Get-Content -Raw -Path $EnvBootstrap

# Generate a unique APP_KEY for this build (installer will regenerate a final one)
$AppKeyBytes = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($AppKeyBytes)
$AppKeyB64 = [Convert]::ToBase64String($AppKeyBytes)
$UniqueAppKey = "base64:$AppKeyB64"
$EnvContent = $EnvContent -replace 'APP_KEY=', "APP_KEY=$UniqueAppKey"

$DestEnvPath = Join-Path $StagingRoot ".env"
Set-Content -Path $DestEnvPath -Value $EnvContent -Encoding UTF8 -NoNewline
Write-Host "  .env (installer-bootstrap) written with unique APP_KEY." -ForegroundColor Green

# 6. Strip dev-only files
Write-Host "`n[6/8] Stripping development artifacts..." -ForegroundColor Yellow
@('tests','AUDIT','.github','.gitattributes','.editorconfig',
  'phpunit.xml','phpunit.mysql.xml','phpstan.neon','phpstan-baseline.neon',
  'vite.config.js','package.json','package-lock.json','jsconfig.json','scripts',
  'deploy','docs') |
ForEach-Object {
    $p = Join-Path $StagingRoot $_
    if (Test-Path $p) { Remove-Item -Recurse -Force $p }
}

# Ensure storage skeleton + bootstrap/cache
@('storage\app\public','storage\framework\cache\data',
  'storage\framework\sessions','storage\framework\views',
  'storage\logs','bootstrap\cache') |
ForEach-Object {
    $d = Join-Path $StagingRoot $_
    if (-not (Test-Path $d)) { New-Item -ItemType Directory -Path $d -Force | Out-Null }
    $gi = Join-Path $d ".gitignore"
    if (-not (Test-Path $gi)) { Set-Content -Path $gi -Value "*`n!.gitignore`n" }
}

# 7. Install production Composer deps
#    --no-scripts skips package:discover which fails without .env (AppServiceProvider boot).
#    The /install wizard runs optimize:clear+package:discover automatically after setup.
Write-Host "`n[7/8] Running composer install --no-dev --optimize-autoloader --no-scripts..." -ForegroundColor Yellow
Push-Location $StagingRoot
try {
    & composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    if ($LASTEXITCODE -ne 0) { throw "composer install failed (exit $LASTEXITCODE)" }
} finally { Pop-Location }
Write-Host "  Done. bootstrap/cache will be populated by the /install wizard after DB setup." -ForegroundColor Green

# Remove any stale bootstrap/cache files (packages.php, services.php, config.php, routes.php)
$cacheDir = Join-Path $StagingRoot "bootstrap\cache"
foreach ($f in @('packages.php','services.php','config.php','routes.php')) {
    $fp = Join-Path $cacheDir $f
    if (Test-Path $fp) { Remove-Item -Force $fp; Write-Host "  Removed stale $f from bootstrap/cache." -ForegroundColor Yellow }
}

# 8. Exclusion audit
# Note: .env IS intentionally included (the installer-bootstrap stub).
Write-Host "`n[8/8] Exclusion audit..." -ForegroundColor Yellow
$Forbidden = @('\.git$','\\tests\\','\\AUDIT\\','node_modules','\.sql$','\.sqlite$','\.bak$','\.key$','installed\.lock$','deploy\\.env\.production\.example','deploy\\.env\.install-bootstrap')
$Violations = @()
Get-ChildItem -Path $StagingRoot -Recurse -Force | ForEach-Object {
    $rel = $_.FullName.Substring($StagingRoot.Length + 1)
    foreach ($pat in $Forbidden) {
        if ($rel -match $pat) { $Violations += "$rel  (matched: $pat)" }
    }
}
if ($Violations.Count -gt 0) {
    Write-Host "AUDIT FAILURE -- forbidden items found:" -ForegroundColor Red
    $Violations | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
    throw "Exclusion check failed."
}
Write-Host "  PASSED -- no forbidden files." -ForegroundColor Green

# Zip
# CreateFromDirectory writes BACKSLASH entry separators on Windows. On Linux /
# cPanel those extract as literal filenames instead of directories. Build the
# archive manually with forward slashes so it extracts correctly on the server.
Write-Host "`n[ZIP] Compressing to $FinalZip..." -ForegroundColor Yellow
if (Test-Path $FinalZip) { Remove-Item -Force $FinalZip }
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$ZipStream = [System.IO.File]::Open($FinalZip, [System.IO.FileMode]::Create)
$ZipArchive = New-Object System.IO.Compression.ZipArchive($ZipStream, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    Get-ChildItem -Path $StagingRoot -Recurse -File -Force | ForEach-Object {
        $rel = $_.FullName.Substring($StagingRoot.Length + 1).Replace([char]92, [char]47)
        $entry = $ZipArchive.CreateEntry($rel, [System.IO.Compression.CompressionLevel]::Optimal)
        $entryStream = $entry.Open()
        $fileStream = [System.IO.File]::OpenRead($_.FullName)
        try { $fileStream.CopyTo($entryStream) } finally { $fileStream.Dispose(); $entryStream.Dispose() }
    }
} finally {
    $ZipArchive.Dispose()
    $ZipStream.Dispose()
}
Remove-Item -Recurse -Force $StagingRoot

$info = Get-Item $FinalZip
$sizeMB = [math]::Round($info.Length / 1MB, 2)
$arc = [System.IO.Compression.ZipFile]::OpenRead($FinalZip)
$entryCount = $arc.Entries.Count
$topLevel = ($arc.Entries | Where-Object { ($_.FullName -split '[/\\]').Count -le 2 } |
             ForEach-Object { ($_.FullName -split '[/\\]')[0] } | Sort-Object -Unique)
$arc.Dispose()

$Sha256 = (Get-FileHash -Path $FinalZip -Algorithm SHA256).Hash.ToLower()
$ShaFile = "$FinalZip.sha256"
"$Sha256 *$ZipName" | Out-File -FilePath $ShaFile -Encoding ascii

Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host " Release archive created successfully!" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "Path      : $FinalZip"
Write-Host "Size      : $sizeMB MB"
Write-Host "SHA256    : $Sha256"
Write-Host "Checksum  : $ShaFile"
Write-Host "Entries   : $entryCount"
Write-Host "Top-level : $($topLevel -join ', ')"
Write-Host "==========================================================" -ForegroundColor Green

