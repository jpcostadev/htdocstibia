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

# Clones de atualizacao antigos usam sparse checkout e ainda nao incluem o painel admin.
if (!(Test-Path -LiteralPath (Join-Path $sourceRoot 'admin\template\template.php')) -and
    (Test-Path -LiteralPath (Join-Path $sourceRoot '.git'))) {
 & git -C $sourceRoot sparse-checkout add admin
 if ($LASTEXITCODE -ne 0) { throw 'Nao foi possivel adicionar a pasta admin ao sparse checkout.' }
}
$files = @(
 'system/templates/serverinfo.html.twig',
 'system/pages/serverinfo.php',
 'system/pages/vip.php', 'system/templates/vip.html.twig',
 'admin/template/template.php', 'admin/template/style.css', 'admin/template/renfall-admin.css',
 'admin/pages/dashboard.php', 'system/templates/admin.dashboard.html.twig', 'system/templates/admin.statistics.html.twig',
 'admin/pages/modules/templates/coins.html.twig', 'admin/pages/modules/templates/coinstransferable.html.twig',
 'admin/pages/modules/templates/lastlogin.html.twig', 'admin/pages/modules/templates/most_donates.html.twig',
 'system/functions.php',
 'templates/tibiacom/boxes/discord.php', 'templates/tibiacom/boxes/renfallboosted.php', 'templates/tibiacom/boxes/renfallguides.php', 'templates/tibiacom/account.management.html.twig', 'templates/tibiacom/account.login.html.twig',
 'templates/tibiacom/index.php', 'templates/tibiacom/config.php', 'templates/tibiacom/renfall.css', 'templates/tibiacom/renfall.js',
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
 $twigCache = [IO.Path]::GetFullPath((Join-Path $targetRoot 'system\cache\twig'))
 if (!$twigCache.StartsWith($targetRoot+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Cache Twig fora do site.' }
 if (Test-Path -LiteralPath $twigCache) { Get-ChildItem -LiteralPath $twigCache -Force | Remove-Item -Recurse -Force }
} catch {
 foreach ($item in $plan) {
  if ($item.Existed) { Copy-Item -LiteralPath (Join-Path $backupRoot $item.Relative) -Destination $item.Target -Force }
  elseif (Test-Path -LiteralPath $item.Target) { Remove-Item -LiteralPath $item.Target -Force }
 }
 throw
}
Write-Host "Renfall atualizado. Backup: $backupRoot" -ForegroundColor Green
Write-Host 'Abra https://renfall.online/admin/ e pressione Ctrl+F5. Nao precisa reiniciar o jogo.'
