param([switch]$OpenBrowser)
$ErrorActionPreference = 'Stop'
$projectDir = Split-Path $PSScriptRoot -Parent
$php = Join-Path $env:USERPROFILE '.config/herd/bin/php84/php.exe'
if (!(Test-Path $php)) { throw 'PHP não encontrado. Instale/inicie o Herd antes de apresentar.' }
Push-Location $projectDir
try {
    & powershell -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'postgresql.ps1') start
    if ($LASTEXITCODE -ne 0) { throw 'Não foi possível iniciar o PostgreSQL.' }
    if (!(Test-Path 'public/build/manifest.json')) { throw 'Interface não compilada. Execute npm.cmd run build.' }
    $url = 'http://127.0.0.1:8000'
    $ready = $false
    try { $ready = (Invoke-WebRequest "$url/up" -UseBasicParsing -TimeoutSec 3).StatusCode -eq 200 } catch {}
    if (!$ready) {
        Start-Process -FilePath $php -ArgumentList 'artisan serve --host=127.0.0.1 --port=8000' -WorkingDirectory $projectDir -WindowStyle Hidden -RedirectStandardOutput (Join-Path $projectDir '.local/demo-server.stdout.log') -RedirectStandardError (Join-Path $projectDir '.local/demo-server.stderr.log')
        for ($attempt = 0; $attempt -lt 15; $attempt++) {
            Start-Sleep -Milliseconds 500
            try { $ready = (Invoke-WebRequest "$url/up" -UseBasicParsing -TimeoutSec 2).StatusCode -eq 200 } catch {}
            if ($ready) { break }
        }
    }
    if (!$ready) { throw 'O site não respondeu na porta 8000. Consulte .local/demo-server.stderr.log.' }
    # Check a database-backed page too; /up alone only verifies application boot.
    $null = Invoke-WebRequest "$url/login" -UseBasicParsing -TimeoutSec 10
    Write-Host "Demonstração local pronta: $url/mostra"
    if ($OpenBrowser) { Start-Process "$url/mostra" }
} finally { Pop-Location }
