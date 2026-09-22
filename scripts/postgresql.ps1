param([ValidateSet('start', 'stop', 'status')][string]$Action = 'start')

$ErrorActionPreference = 'Stop'
$projectDir = Split-Path $PSScriptRoot -Parent
$pgCtl = Join-Path $projectDir '.local/postgresql/pgsql/bin/pg_ctl.exe'
$dataDir = Join-Path $projectDir '.local/pgdata'
$logFile = Join-Path $projectDir '.local/postgresql.log'
if (!(Test-Path $pgCtl) -or !(Test-Path (Join-Path $dataDir 'PG_VERSION'))) {
    throw 'A instancia local nao foi instalada. Consulte POSTGRESQL.md.'
}
& $pgCtl -D $dataDir status
$running = $LASTEXITCODE -eq 0
if ($Action -eq 'status') { exit $LASTEXITCODE }
if (($Action -eq 'start' -and $running) -or ($Action -eq 'stop' -and !$running)) { exit 0 }
$arguments = if ($Action -eq 'start') {
    '-D "{0}" -l "{1}" -w start' -f $dataDir, $logFile
} else {
    '-D "{0}" -m fast -w stop' -f $dataDir
}
$stdout = Join-Path $projectDir '.local/control.stdout'
$stderr = Join-Path $projectDir '.local/control.stderr'
$process = Start-Process -FilePath $pgCtl -ArgumentList $arguments -WindowStyle Hidden -PassThru -RedirectStandardOutput $stdout -RedirectStandardError $stderr
# Wait only for pg_ctl, not for the persistent PostgreSQL descendant process.
$process.WaitForExit()
Get-Content $stdout
Get-Content $stderr
exit $process.ExitCode
