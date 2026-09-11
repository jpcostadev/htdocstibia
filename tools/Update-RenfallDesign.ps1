param(
 [string]$WebRoot = 'C:\Users\Administrador\Desktop\RenfallzOT\TibiaOtCrystal\site',
 [string]$Php = 'C:\xampp\php\php.exe',
 [switch]$CheckOnly
)
$ErrorActionPreference = 'Stop'
$sourceRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..')).TrimEnd('\')
$targetRoot = (Resolve-Path -LiteralPath $WebRoot).Path.TrimEnd('\')
if (!(Test-Path -LiteralPath (Join-Path $targetRoot 'system\libs\crystal_bazaar.php'))) { throw 'Este pacote exige o bazaar Crystal instalado. Nenhum arquivo alterado.' }
if (!(Test-Path -LiteralPath $Php)) { throw "PHP nao encontrado em $Php. Informe -Php com o caminho correto." }
$files = @(
 'templates/tibiacom/boxes/discord.php', 'templates/tibiacom/boxes/renfallboosted.php', 'templates/tibiacom/boxes/renfallguides.php', 'templates/tibiacom/account.management.html.twig',
 'templates/tibiacom/index.php', 'templates/tibiacom/renfall.css', 'templates/tibiacom/renfall.js',
 'templates/tibiacom/boxes/donate.php', 'templates/tibiacom/config.ini', 'templates/tibiacom/images/header/renfall-logo.png',
 'system/pages/crystalbazaar.php', 'system/pages/renfall_bazaar_details.php', 'system/libs/renfall_bazaar_catalog.php',
 'system/pages/currentcharactertrades.php', 'system/pages/pastcharactertrades.php', 'system/pages/owncharactertrades.php', 'system/pages/createcharacterauction.php'
)
$plan = @()
foreach ($file in $files) {
 $src = [IO.Path]::GetFullPath((Join-Path $sourceRoot $file))
 $dst = [IO.Path]::GetFullPath((Join-Path $targetRoot $file))
 if (!$src.StartsWith($sourceRoot+'\',[StringComparison]::OrdinalIgnoreCase) -or !$dst.StartsWith($targetRoot+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Caminho fora do site.' }
 if (!(Test-Path -LiteralPath $src)) { throw "Arquivo ausente: $file. Atualize o sparse checkout." }
 if ($src.EndsWith('.php')) { & $Php -l $src; if ($LASTEXITCODE -ne 0) { throw "PHP invalido: $file" } }
 $plan += [pscustomobject]@{Source=$src;Target=$dst;Relative=$file;Existed=(Test-Path -LiteralPath $dst)}
}
if ($CheckOnly) { Write-Host 'Verificacao concluida. Nenhum arquivo alterado.' -ForegroundColor Green; return }
$backupRoot = Join-Path (Split-Path $targetRoot -Parent) ('renfall-design-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss-fff'))
New-Item -ItemType Directory -Path $backupRoot | Out-Null
foreach ($item in $plan) {
 if ($item.Existed) { $backup=Join-Path $backupRoot $item.Relative; New-Item -ItemType Directory -Force -Path (Split-Path $backup) | Out-Null; Copy-Item -LiteralPath $item.Target -Destination $backup }
}
$plan | Select-Object Relative,Existed | ConvertTo-Json | Set-Content -LiteralPath (Join-Path $backupRoot 'files.json') -Encoding UTF8
try {
 foreach ($item in $plan) { New-Item -ItemType Directory -Force -Path (Split-Path $item.Target) | Out-Null; Copy-Item -LiteralPath $item.Source -Destination $item.Target -Force }
 foreach ($item in $plan) { if ((Get-FileHash -LiteralPath $item.Source).Hash -ne (Get-FileHash -LiteralPath $item.Target).Hash) { throw "Falha na copia: $($item.Relative)" } }
} catch {
 foreach ($item in $plan) {
  if ($item.Existed) { Copy-Item -LiteralPath (Join-Path $backupRoot $item.Relative) -Destination $item.Target -Force }
  elseif (Test-Path -LiteralPath $item.Target) { Remove-Item -LiteralPath $item.Target -Force }
 }
 throw
}
Write-Host "Renfall atualizado. Backup: $backupRoot" -ForegroundColor Green
Write-Host 'Abra https://renfall.online/?news e pressione Ctrl+F5. Nao precisa reiniciar o jogo.'
