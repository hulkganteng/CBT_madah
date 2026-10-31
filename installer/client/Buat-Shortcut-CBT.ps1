<#
    Buat dua shortcut ujian di desktop setiap PC client (PRD FR-29, Bab 7.2):
      1. "CBT Online"          -> domain HTTPS resmi
      2. "CBT Lokal (Darurat)" -> alamat LAN server

    Setiap shortcut menjalankan Safe Exam Browser dengan berkas .seb miliknya
    sendiri bila tersedia. Bila .seb belum ada, jatuh ke Chrome/Edge mode kiosk
    supaya PC tetap bisa dipakai pada simulasi.

    Jalankan sekali per PC client, sebagai user yang akan memakai PC:

      powershell -ExecutionPolicy Bypass -File .\Buat-Shortcut-CBT.ps1 `
        -OnlineUrl "https://cbt.bhydev.my.id/" `
        -LanUrl    "http://192.168.1.10/garudacbt/" `
        -SebOnline "C:\CBT\SEB-Online.seb" `
        -SebLokal  "C:\CBT\SEB-Lokal.seb"

    Untuk 25 PC, jalankan lewat Veyon (Execute command on selected computers).
#>
param(
    [Parameter(Mandatory = $true)][string] $OnlineUrl,
    [Parameter(Mandatory = $true)][string] $LanUrl,
    [string] $SebOnline = '',
    [string] $SebLokal = '',
    [string] $Chrome = "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
    [string] $SebApp = "$env:ProgramFiles\SafeExamBrowser\Application\SafeExamBrowser.exe"
)

$ErrorActionPreference = 'Stop'

# Edge dipakai bila Chrome tidak ada (Windows 11 tanpa Chrome).
if (-not $SebApp -or -not (Test-Path $SebApp)) {
    $SebApp = ''
}
if (-not (Test-Path $Chrome)) {
    $edge = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
    if (Test-Path $edge) { $Chrome = $edge }
}

function New-Shortcut {
    param([string] $Name, [string] $Url, [string] $Seb)

    if ($Seb -and (Test-Path $Seb) -and $SebApp) {
        $target = $SebApp
        $argLine = "-c `"$Seb`""
    }
    elseif (Test-Path $Chrome) {
        $target = $Chrome
        $argLine = "--kiosk --app=$Url --no-first-run --disable-pinch --overscroll-history-navigation=0"
    }
    else {
        throw "Tidak ada Safe Exam Browser maupun Chrome/Edge di PC ini."
    }

    $link = Join-Path ([Environment]::GetFolderPath('Desktop')) "$Name.lnk"
    $shell = New-Object -ComObject WScript.Shell
    $sc = $shell.CreateShortcut($link)
    $sc.TargetPath = $target
    $sc.Arguments = $argLine
    $sc.WorkingDirectory = Split-Path $target
    $sc.Description = "Garuda CBT - $Name"
    $sc.Save()
    Write-Host "OK  $link  ->  $target $argLine"
}

New-Shortcut -Name 'CBT Online' -Url $OnlineUrl -Seb $SebOnline
New-Shortcut -Name 'CBT Lokal (Darurat)' -Url $LanUrl -Seb $SebLokal

Write-Host ''
Write-Host 'Selesai. Uji kedua shortcut sebelum hari ujian (login, token, autosave, kumpul).'
