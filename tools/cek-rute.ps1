# Cek cepat: rute CBT murni harus 200, rute non-lingkup harus 403.
# Pakai: powershell -File tools\cek-rute.ps1 -BaseUrl http://localhost/garudacbt/ -User admin -Pass admin123
param(
    [string] $BaseUrl = 'http://localhost/garudacbt/',
    [Parameter(Mandatory = $true)][string] $User,
    [Parameter(Mandatory = $true)][string] $Pass
)

$ErrorActionPreference = 'Stop'
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

function Get-Csrf {
    $page = Invoke-WebRequest -Uri ($BaseUrl + 'auth') -WebSession $session -UseBasicParsing
    $m = [regex]::Match($page.Content, 'name="csrf_token"\s+value="([^"]+)"')
    if (-not $m.Success) { throw 'csrf_token tidak ditemukan di halaman login.' }
    return $m.Groups[1].Value
}

$token = Get-Csrf
$login = Invoke-WebRequest -Uri ($BaseUrl + 'auth/cek_login') -Method POST -WebSession $session `
    -Headers @{ 'X-Requested-With' = 'XMLHttpRequest' } -UseBasicParsing `
    -Body @{ identity = $User; password = $Pass; csrf_token = $token }
if ($login.Content -notmatch '"status"\s*:\s*true') { throw "Login gagal: $($login.Content)" }

$harusBuka = @(
    'dashboard', 'examactivity', 'cbtbanksoal', 'datamapel', 'cbtjadwal',
    'dbpemulihan', 'dbmanager', 'cbtstatus', 'cbtnilai', 'cbtrekap',
    'cbtanalisis', 'cbtcetak', 'useradmin', 'userguru', 'usersiswa', 'settings'
)
$harusTutup = @(
    'dbclear', 'dbmanager/truncate', 'rapor', 'bukurapor', 'bukuinduk',
    'dataalumni', 'kelasmateri', 'kelasmaterijadwal', 'kelascatatan',
    'kelasjadwal', 'kelasstatus', 'kelasnilai', 'kelasabsensiharian',
    'kelasabsensibulanan', 'walicatatan', 'users', 'jurusanmapel',
    'hasilujian', 'installer/'
)

$gagal = 0
foreach ($rute in $harusBuka) {
    $kode = (Invoke-WebRequest -Uri ($BaseUrl + $rute) -WebSession $session -UseBasicParsing).StatusCode
    if ($kode -ne 200) { Write-Host "GAGAL $rute => $kode (harus 200)"; $gagal++ }
}
foreach ($rute in $harusTutup) {
    try {
        $kode = (Invoke-WebRequest -Uri ($BaseUrl + $rute) -WebSession $session -UseBasicParsing).StatusCode
    }
    catch { $kode = [int] $_.Exception.Response.StatusCode }
    if ($kode -ne 403) { Write-Host "GAGAL $rute => $kode (harus 403)"; $gagal++ }
}

if ($gagal -eq 0) { Write-Host "OK: $($harusBuka.Count) rute terbuka, $($harusTutup.Count) rute tertutup." }
exit $gagal
