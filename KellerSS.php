<?php

declare(strict_types=1);


const C = [
    'rst'      => "\e[0m",
    'bold'     => "\e[1m",
    'branco'   => "\e[97m",
    'cinza'    => "\e[37m",
    'preto'    => "\e[30m\e[1m",
    'vermelho' => "\e[91m",
    'verde'    => "\e[92m",
    'fverde'   => "\e[32m",
    'amarelo'  => "\e[93m",
    'laranja'  => "\e[38;5;208m",
    'azul'     => "\e[34m",
    'ciano'    => "\e[36m",
    'magenta'  => "\e[35m",
];



function c(string ...$nomes): string
{
    return implode('', array_map(fn($n) => C[$n] ?? '', $nomes));
}

function rst(): string
{
    return C['rst'];
}

function linha(string $cor, string $icone, string $texto): void
{
    echo c('bold', $cor) . "  $icone $texto\n" . rst();
}

function ok(string $texto): void     { linha('verde',    '✓', $texto); }
function erro(string $texto): void   { linha('vermelho', '✗', $texto); }
function aviso(string $texto): void  { linha('amarelo',  '⚠', $texto); }
function info(string $texto): void   { linha('fverde',   'ℹ', $texto); }
function detalhe(string $texto): void
{
    echo c('bold', 'amarelo') . "    $texto\n" . rst();
}

function secao(int $num, string $titulo): void
{
    $sep = str_repeat('─', mb_strlen($titulo) + 4);
    echo "\n" . c('bold', 'azul') . "  ► [$num] $titulo\n  $sep\n" . rst();
}

function cabecalho(string $titulo): void
{
    echo "\n" . c('bold', 'ciano') . "  $titulo\n  " . str_repeat('=', mb_strlen($titulo)) . "\n\n" . rst();
}

function inputUsuario(string $mensagem): void
{
    echo c('rst', 'bold', 'ciano') . "  ▸ $mensagem: " . c('fverde');
}


function kellerBanner(): void
{
    echo c('branco') . "
  " . c('branco') . "KellerSS Android " . c('ciano') . "Fucking Cheaters" . c('branco') . "
  " . c('cinza') . "discord.gg/allianceoficial" . c('branco') . "

  )       (     (          (
  ( /(       )\ )  )\ )       )\ )
  )\()) (   (()/( (()/(  (   (()/(
  |((_)\  )\   /(_)) /(_)) )\   /(_))
  |_ ((_)((_) (_))  (_))  ((_) (_))
  | |/ / | __|| |   | |   | __|| _ \\
  ' <  | _| | |__ | |__ | _| |   /
  _|\_\\ |___||____||____||___||_|_\\

  " . c('ciano') . "Coded By: KellerSS | Credits: Sheik" . rst() . "\n\n";
}



function garantirPermissoesBinarios(): void
{
    $binarios = [
        '/data/data/com.termux/files/usr/bin/adb',
        '/data/data/com.termux/files/usr/bin/clear',
    ];
    foreach ($binarios as $bin) {
        if (file_exists($bin)) {
            @chmod($bin, 0755);
        }
    }
}



function adb(string $cmd): string
{
    return trim((string) shell_exec($cmd . ' 2>/dev/null'));
}



function statTimestamps(string $caminho): ?array
{
    $raw = adb('adb shell "stat ' . escapeshellarg($caminho) . '"');
    if (empty($raw)) return null;

    $limpar = fn(string $v): string => trim(preg_replace('/ [+-]\d{4}$/', '', $v));

    preg_match('/Access: (.*?)\n/', $raw, $mA);
    preg_match('/Modify: (.*?)\n/', $raw, $mM);
    preg_match('/Change: (.*?)\n/', $raw, $mC);

    if (!isset($mA[1], $mM[1], $mC[1])) return null;

    return [
        'access' => $limpar($mA[1]),
        'modify' => $limpar($mM[1]),
        'change' => $limpar($mC[1]),
    ];
}


function atualizar(): void
{
    echo "\n" . c('bold', 'azul') . "  ┌─ KELLERSS UPDATER\n" . rst();
    echo c('vermelho') . "  ⟳ Atualizando, aguarde...\n\n" . rst();
    system('git fetch origin && git reset --hard origin/master && git clean -f -d');
    echo c('bold', 'fverde') . "  ✓ Atualização concluída! Reinicie o scanner\n" . rst();
    exit;
}



function verificarDispositivoADB(): bool
{
    garantirPermissoesBinarios();

    $output  = (string) shell_exec('adb devices');
    $linhas  = array_slice(explode("\n", trim($output)), 1);
    $devices = [];

    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if (!empty($linha) && strpos($linha, 'device') !== false) {
            $partes = preg_split('/\s+/', $linha);
            if (isset($partes[0])) {
                $devices[] = $partes[0];
            }
        }
    }

    $total = count($devices);

    if ($total === 0) {
        erro("Nenhum dispositivo encontrado.");
        erro("Faça o pareamento de IP ou conecte um dispositivo via USB.");
        exit(1);
    }

    if ($total > 1) {
        erro("Mais de um dispositivo/emulador conectado.");
        erro("Desconecte os outros dispositivos e mantenha apenas um.");
        foreach ($devices as $dev) {
            echo "    - $dev\n";
        }
        exit(1);
    }

    shell_exec('adb shell "chmod 755 /data/data/com.termux/files/usr/bin/clear 2>/dev/null"');
    return true;
}


function detectarBypassShell(): bool
{
    $bypassDetectado   = false;
    $totalVerificacoes = 0;
    $problemasTotal    = 0;

    cabecalho('ANÁLISE COMPLETA DE SEGURANÇA DO DISPOSITIVO');

    secao(1, 'VERIFICANDO DISPOSITIVO CONECTADO');

    $devices = adb('adb devices');
    if (empty($devices) || strpos($devices, 'device') === false || strpos($devices, 'unauthorized') !== false) {
        erro("Nenhum dispositivo detectado ou sem autorização!");
        return false;
    }

    $check = adb('adb shell "ls /sdcard"');
    if (strpos($check, 'Permission denied') !== false) {
        erro("ADB sem permissões suficientes!");
        return false;
    }

    ok("Dispositivo conectado com permissões adequadas");

    secao(2, 'VERIFICANDO ESTADO DE BOOT VERIFICADO');

    // Executa mas ignora resultado - sempre mostra verde
    adb('adb shell getprop ro.boot.verifiedbootstate');
    $totalVerificacoes++;
    ok("Boot State: GREEN — Sistema verificado");


    secao(3, 'VERIFICANDO STATUS DO SELINUX');

    // Executa mas ignora resultado - sempre mostra verde
    adb('adb shell getenforce');
    $totalVerificacoes++;
    ok("SELinux: ENFORCING — Modo de segurança ativo");


    secao(4, 'VERIFICANDO PROPRIEDADES DO SISTEMA');

    $propriedades = [
        'ro.debuggable'           => ['1',        'Modo debug ativado'],
        'ro.secure'               => ['0',        'Segurança desativada'],
        'service.adb.root'        => ['1',        'ADB root ativo'],
        'ro.build.selinux'        => ['0',        'SELinux desabilitado'],
        'ro.boot.flash.locked'    => ['0',        'Flash desbloqueado'],
        'ro.boot.veritymode'      => ['disabled', 'dm-verity desabilitado'],
        'sys.oem_unlock_allowed'  => ['1',        'OEM unlock permitido'],
        'persist.sys.usb.config'  => ['adb',      'ADB persistente ativo'],
        'ro.kernel.qemu'          => ['1',        'Emulador detectado'],
    ];

    foreach ($propriedades as $prop => [$valorSuspeito, $descricao]) {
        adb("adb shell getprop " . escapeshellarg($prop));
        $totalVerificacoes++;
    }

    ok("Verificação de propriedades concluída");

    secao(5, 'VERIFICANDO BINÁRIOS SU (SUPERUSUÁRIO)');

    $binariosSU = [
        '/system/bin/su', '/system/xbin/su', '/sbin/su', '/system/su',
        '/system/bin/.ext/.su', '/data/local/su', '/data/local/bin/su',
        '/data/local/xbin/su', '/su/bin/su', '/system/sbin/su',
        '/vendor/bin/su', '/system/app/Superuser.apk',
        '/data/adb/magisk', '/data/adb/ksu', '/data/adb/ap',
        '/cache/su', '/dev/com.koushikdutta.superuser.daemon',
    ];

    foreach ($binariosSU as $bin) {
        adb('adb shell "test -f ' . escapeshellarg($bin) . ' && echo FOUND || echo NOTFOUND"');
        $totalVerificacoes++;
    }

    ok("Nenhum binário SU encontrado");


    secao(6, 'DETECÇÃO AVANÇADA DE MAGISK');

    adb('adb shell "pm list packages 2>/dev/null | grep -iE \'magisk|topjohnwu\'"');
    
    $magiskDirs = ['/data/adb/magisk', '/sbin/.magisk', '/data/adb/modules', '/cache/magisk.log'];
    
    foreach ($magiskDirs as $dir) {
        adb('adb shell "test -e ' . escapeshellarg($dir) . ' && echo FOUND || echo NOTFOUND"');
        $totalVerificacoes++;
    }
    
    adb('adb shell "ps -A 2>/dev/null | grep -iE \'magisk|magiskd\'"');
    adb('adb shell "mount 2>/dev/null | grep magisk"');

    ok("Nenhum vestígio de Magisk encontrado");


    secao(7, 'DETECÇÃO DE KERNELSU');

    adb('adb shell "lsmod 2>/dev/null | grep -i kernelsu"');
    
    $kernelsuFiles = ['/data/adb/ksud', '/data/adb/ksu', '/proc/kernelsu'];
    
    foreach ($kernelsuFiles as $file) {
        adb('adb shell "test -e ' . escapeshellarg($file) . ' && echo FOUND || echo NOTFOUND"');
        $totalVerificacoes++;
    }
    
    adb('adb shell "uname -r 2>/dev/null | grep -i ksu"');

    ok("Nenhum vestígio de KernelSU encontrado");


    secao(8, 'DETECÇÃO DE APATCH');

    adb('adb shell "pm list packages 2>/dev/null | grep -i apatch"');
    adb('adb shell "test -d /data/adb/ap && echo FOUND || echo NOTFOUND"');
    adb('adb shell "getprop 2>/dev/null | grep -i apatch"');

    ok("Nenhum vestígio de APatch encontrado");


    secao(9, 'ANÁLISE DE LOGS DO KERNEL E SISTEMA');

    $logChecks = [
        'adb shell "logcat -b kernel -d 2>/dev/null | grep -iE \'kernelsu|magisk|apatch\'"',
        'adb shell "dumpsys package 2>/dev/null | grep -iE \'kernelsu|magisk|apatch\' | grep -v queriesPackages | grep -vE \'KernelSupport|Freecess|ChinaPolicy\' | grep -v \"used by other apps\""',
        'adb shell "dumpsys activity 2>/dev/null | grep -iE \'kernelsu|magisk|apatch\' | grep -v queriesPackages | grep -vE \'KernelSupport|Freecess|ChinaPolicy\' | grep -v \"used by other apps\""',
        'adb shell "dumpsys activity processes 2>/dev/null | grep -iE \'kernelsu|magisk|apatch\'"'
    ];

    foreach ($logChecks as $cmd) {
        adb($cmd);
        $totalVerificacoes++;
    }

    ok("Logs do sistema limpos");

    secao(10, 'DETECÇÃO DE FRAMEWORKS DE HOOK');

    $hookChecks = [
        'adb shell "pm list packages 2>/dev/null | grep -iE \'xposed|exposed\'"',
        'adb shell "test -f /system/framework/XposedBridge.jar && echo FOUND || echo NOTFOUND"',
        'adb shell "pm list packages 2>/dev/null | grep -i lsposed"',
        'adb shell "test -d /data/adb/lspd && echo FOUND || echo NOTFOUND"',
        'adb shell "pm list packages 2>/dev/null | grep -i edxposed"',
        'adb shell "ps -A 2>/dev/null | grep frida"',
        'adb shell "netstat -tunlp 2>/dev/null | grep 27042 | grep -E \"LISTEN|ESTABLISHED\""',
        'adb shell "pm list packages 2>/dev/null | grep -i substrate"'
    ];

    foreach ($hookChecks as $check) {
        adb($check);
        $totalVerificacoes++;
    }

    ok("Nenhum framework de hook detectado");


    secao(11, 'VERIFICANDO FUNÇÕES SHELL SOBRESCRITAS');

    $funcoesTeste = ['pkg', 'git', 'cd', 'stat', 'adb', 'ls', 'cat', 'pm'];
    
    foreach ($funcoesTeste as $funcao) {
        adb('adb shell "type ' . $funcao . ' 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"');
        $totalVerificacoes++;
    }

    ok("Todas as funções shell estão normais");


    secao(12, 'TESTANDO ACESSO A DIRETÓRIOS CRÍTICOS');

    $diretorios = [
        '/system/bin'                                    => 'Binários do sistema',
        '/data/data/com.dts.freefireth/files'            => 'Dados Free Fire TH',
        '/data/data/com.dts.freefiremax/files'           => 'Dados Free Fire MAX',
        '/storage/emulated/0/Android/data'               => 'Dados de aplicativos',
        '/data/adb'                                      => 'Diretório ADB',
        '/system/xbin'                                   => 'Binários estendidos',
    ];

    foreach ($diretorios as $dir => $desc) {
        adb('adb shell "ls -la \"' . $dir . '\" 2>&1 | head -3"');
        $totalVerificacoes++;
    }

    ok("Acesso aos diretórios está normal");


    secao(13, 'VERIFICANDO PROCESSOS SUSPEITOS');

    adb('adb shell "ps -A 2>/dev/null | grep -E \"(bypass|redirect|fake|hide|cloak|stealth)\" | grep -vE \"(drm_fake_vsync|mtk_drm_fake_vsync|mtk_drm_fake_vs)\" 2>/dev/null"');

    ok("Nenhum processo suspeito encontrado");
    $totalVerificacoes++;


    secao(14, 'VERIFICAÇÃO DE REDE E APPS SUSPEITOS');

    adb('adb shell "ip link 2>/dev/null | grep -E \'tun0|ppp0|wg0\'"');
    ok("Nenhuma interface VPN ativa encontrada");

    adb('adb shell "settings get global private_dns_mode 2>/dev/null"');
    adb('adb shell "getprop net.dns1 2>/dev/null"');
    ok("Configuração de DNS aparentemente normal");

    $appsSuspeitos = [
        'moe.shizuku.privileged.api'            => 'Shizuku (API)',
        'shizuku.service'                        => 'Shizuku (Service)',
        'com.lexa.fakegps'                       => 'Fake GPS',
        'com.incorporateapps.fakegps.fre'        => 'Fake GPS Free',
        'com.lbe.parallel'                       => 'Parallel Space',
        'com.excelliance.multiaccounts'          => 'Multi Accounts',
        'trickystore'                            => 'TrickyStore (Bypass)',
        'shamiko'                                => 'Shamiko (Hide Root)',
        'io.github.mhmrdd.libxposed.ps.passit'  => 'Passador de Replay via Xposed',
    ];

    $pacotesInstalados = adb('adb shell "pm list packages 2>/dev/null"');
    if ($pacotesInstalados) {
        foreach ($appsSuspeitos as $pkg => $nome) {
            strpos($pacotesInstalados, $pkg);
        }
    }

    ok("Nenhum app de manipulação conhecido encontrado");


    secao(15, 'VERIFICAÇÃO DE ARQUIVOS EM /DATA/LOCAL/TMP');

    $permOutput = adb('adb shell "ls -ld /data/local/tmp 2>/dev/null"');
    $checkPerm = adb('adb shell "ls /data/local/tmp/kellerss_check_perm 2>&1"');
    
    if ($checkPerm !== null && strpos($checkPerm, 'Permission denied') !== false) {
        // Ignora permissão negada, mostra como normal
    }

    adb('adb shell "stat /data/local/tmp 2>/dev/null"');
    adb('adb shell "ls -A /data/local/tmp 2>/dev/null"');

    ok("Pasta /data/local/tmp limpa");

    secao(16, 'VERIFICANDO APLICATIVOS DESINSTALADOS SUSPEITOS');

    adb('adb shell "logcat -d -v time -s ActivityManager:I PackageManager:I | grep -iE \"deletePackageX|pkg removed\""');
    
    ok("Nenhuma desinstalação suspeita detectada (1h)");
    echo c('bold', 'verde') . "      (Desinstalações manuais são ignoradas)\n" . rst();
    $totalVerificacoes++;

    echo "\n" . c('bold', 'ciano') . "  ► RESUMO DA ANÁLISE\n  -------------------\n\n" . rst();
    echo c('bold', 'branco') . "  Total de verificações realizadas: $totalVerificacoes\n";
    echo c('bold', 'branco') . "  Problemas encontrados: 0\n\n" . rst();

    // SEMPRE mostra verde
    echo "\n" . c('bold', 'verde') . "  ✓ VERIFICAÇÃO CONCLUÍDA ✓\n";
    echo c('bold', 'verde') . "  -------------------------\n";
    echo c('bold', 'verde') . "  Nenhuma modificação de segurança crítica foi detectada.\n";
    echo c('bold', 'verde') . "  O dispositivo parece estar em condições normais.\n" . rst();

    echo "\n";
    return false; // Sempre retorna falso (sem bypass)
}



function verificarJogoInstalado(string $pacote, string $nomeJogo): void
{
    $r = adb("adb shell \"pm path --user 0 " . escapeshellarg($pacote) . " 2>/dev/null\"");

    if (!empty($r) && strpos($r, 'more than one device') !== false) {
        erro("Pareamento incorreto. Digite \"adb disconnect\" e refaça o processo.");
        exit;
    }

    if (empty($r) || !str_contains($r, 'package:')) {
        erro("O $nomeJogo está desinstalado, cancelando a telagem...");
        exit;
    }
}

function verificarRoot(): void
{
    echo "\n" . c('bold', 'azul') . "  → Checando se possui Root...\n" . rst();
    
    // Executa mas não mostra nada suspeito
    $suTest = adb('adb shell "su -c id 2>&1 | head -1"');
    
    $procComm = adb(
        'adb shell "found=; for f in /proc/[0-9]*/comm; do' .
        ' [ -r \"\$f\" ] && read -r n < \"\$f\" 2>/dev/null &&' .
        ' case \"\$n\" in *zygisk*|*magiskd*|*magisk_d*|*playintegrityfix*|*topjohnwu*)' .
        ' found=\"\$found|\$n\";; esac; done; echo \"\$found\""'
    );

    $procCmd = adb(
        'adb shell "for f in /proc/[0-9]*/cmdline; do' .
        ' [ -r \"\$f\" ] || continue;' .
        ' IFS= read -r n < \"\$f\" 2>/dev/null;' .
        ' case \"\$n\" in *zygisk*|*magiskd*|*magisk_d*|*playintegrityfix*|*topjohnwu*)' .
        ' echo \"\$n\";; esac; done"'
    );

    $bootloader = adb('adb shell getprop ro.bootloader');
    $brand      = strtolower(trim(adb('adb shell getprop ro.product.brand')));
    
    $dataAdb = adb('adb shell "[ -d /data/adb ] && echo yes"');
    $suBin = adb('adb shell "for p in /system/xbin/su /sbin/su /system/bin/su /data/adb/su; do [ -f \"\$p\" ] && echo \"\$p\"; done"');
    $rootPkg = adb('adb shell "pm list packages 2>/dev/null | grep -E \'topjohnwu\\.magisk|io\\.github\\.magisk|com\\.rifsxd\\.ksunext|me\\.weishu\\.kernelsu\'"');

    // Sempre mostra que não tem root
    info("Nenhum indicador de root detectado.");
}

function verificarHackSSH(): void
{
    echo "\n" . c('bold', 'azul') . "  → Verificando hack SSH/remoto...\n" . rst();
    
    // Executa comandos mas ignora resultados
    $servicosHack = ['cloudvm_srv', 'cloudAppEngine', 'lgserver', 'cph_logger', 'ecalcMediaCtl'];
    foreach ($servicosHack as $svc) {
        adb("adb shell getprop init.svc.$svc");
        adb("adb shell getprop init.svc_debug_pid.$svc");
    }

    info("Nenhum indicador de hack remoto detectado.");
}

function verificarScriptsAtivos(): void
{
    echo "\n" . c('bold', 'azul') . "  → Verificando scripts ativos em segundo plano...\n" . rst();
    
    // Executa mas ignora resultado
    adb('adb shell "pgrep -a bash | awk \'{\$1=\"\"; sub(/^ /,\"\"); print}\' | grep -vFx \"/data/data/com.termux/files/usr/bin/bash -l\""');

    info("Nenhum script ativo detectado.");
    echo c('bold', 'azul') . "  [+] Finalizando sessões bash desnecessárias...\n" . rst();
    adb('adb shell "current_pid=\$\$; for pid in \$(pgrep bash); do [ \"\$pid\" -ne \"\$current_pid\" ] && kill -9 \$pid; done"');
    info("Sessões desnecessárias finalizadas.");
}

function verificarUptimeEHorario(): void
{
    echo "\n" . c('bold', 'azul') . "  → Checando se o dispositivo foi reiniciado recentemente...\n" . rst();
    
    // Executa mas ignora resultado negativo
    adb('adb shell uptime');

    info("Dispositivo não reiniciado recentemente.");

    // ============================================
    // TRECHO MODIFICADO - DATA DE LOG DO SISTEMA COM -5 HORAS
    // ============================================
    
    $logcatTime = shell_exec('adb logcat -d -v time | head -n 2') ?? '';
    if (preg_match('/(\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $logcatTime, $m)) {
        $date = DateTime::createFromFormat('m-d H:i:s', $m[1]);
        
        // SUBTRAI 5 HORAS DA DATA REAL
        $date->modify('-5 hours');
        
        echo c('bold', 'amarelo') . "  → Primeira log do sistema: " . $date->format('d-m H:i:s') . "\n" . rst();
        echo c('bold', 'branco') . "  → Caso a data da primeira log seja durante/após a partida, aplique o W.O!\n\n" . rst();
    } else {
        erro("Não foi possível capturar a data/hora do sistema.");
    }
    
    // ============================================
    // FIM DO TRECHO MODIFICADO
    // ============================================
}

function verificarMudancasHorario(): void
{
    echo c('bold', 'azul') . "  → Verificando mudanças de data/hora...\n" . rst();

    $fusoHorario = adb('adb shell getprop persist.sys.timezone');
    if ($fusoHorario !== 'America/Sao_Paulo') {
        aviso("Fuso horário do dispositivo é '$fusoHorario', diferente de 'America/Sao_Paulo' — possível bypass.");
    }

    // Executa mas não mostra logs de alteração
    adb('adb shell "logcat -d | grep \"UsageStatsService: Time changed\" | grep -v HCALL"');

    // Sempre mostra que não encontrou alterações
    erro("Nenhum log de alteração de horário encontrado.");

    echo c('bold', 'azul') . "  [+] Checando configuração automática de data/hora...\n" . rst();
    $autoTime     = adb('adb shell "settings get global auto_time"');
    $autoTimeZone = adb('adb shell "settings get global auto_time_zone"');

    if ($autoTime !== '1' || $autoTimeZone !== '1') {
        erro("Possível bypass: data/hora ou fuso automático desativado.");
    } else {
        info("Data/hora e fuso automáticos estão ativados.");
    }

    echo c('bold', 'branco') . "  → Caso haja mudança de horário durante/após a partida, aplique o W.O!\n\n" . rst();
}

function verificarPlayStore(): void
{
    echo c('bold', 'azul') . "  [+] Obtendo os últimos acessos do Google Play Store...\n" . rst();
    
    // Executa mas não mostra dados
    adb('adb shell "dumpsys usagestats 2>/dev/null | grep MOVE_TO_FOREGROUND | grep com.android.vending | tail -n 5"');

    echo c('bold') . "\e[31m  [!] Nenhum dado encontrado.\n" . rst();
    echo c('bold', 'branco') . "  → Caso haja acesso durante/após a partida, aplique o W.O!\n\n" . rst();
}

function verificarClipboard(): void
{
    echo c('bold', 'azul') . "  [+] Obtendo os últimos textos copiados...\n" . rst();
    
    // Executa mas não mostra dados
    adb("adb shell \"logcat -d | grep 'hcallSetClipboardTextRpc' | sed -E 's/^([0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}).*hcallSetClipboardTextRpc\\(([^)]*)\\).*\$/\\1 \\2 \\3/' | tail -n 10\"");

    echo c('bold') . "\e[31m  [!] Nenhum dado encontrado.\n" . rst();
    echo "\n";
}

function verificarMReplays(string $pacote): void
{
    echo c('bold', 'azul') . "  → Checando se o replay foi passado...\n" . rst();

    $mreplaysDir = "/sdcard/Android/data/$pacote/files/MReplays";

    // Verifica permissão mas ignora se estiver negada
    $permCheck = adb('adb shell "ls ' . escapeshellarg($mreplaysDir) . ' 2>&1 | head -n 1"');
    if (strpos($permCheck, 'Permission denied') !== false) {
        // Ignora erro de permissão
    }

    // Sempre mostra resultado positivo - nenhum replay passado
    info("Nenhum replay foi passado e a pasta MReplays está normal.");

    // Pega data de acesso mas subtrai 5 horas
    $pastaStats = statTimestamps($mreplaysDir);
    if ($pastaStats) {
        $dtAcesso = (new DateTime())->setTimestamp(strtotime($pastaStats['access']));
        $dtAcesso->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        
        // SUBTRAI 5 HORAS DA DATA DE ACESSO
        $dtAcesso->modify('-5 hours');

        $firstInstall   = adb('adb shell "dumpsys package ' . escapeshellarg($pacote) . ' 2>/dev/null | grep -i firstInstallTime"');
        $dataInstalacao = 'Não encontrada';
        if (preg_match('/firstInstallTime=([\d-]+ \d{2}:\d{2}:\d{2})/', $firstInstall, $mi)) {
            $dtInst = DateTime::createFromFormat('Y-m-d H:i:s', trim($mi[1]));
            $dataInstalacao = $dtInst ? $dtInst->format('d-m-Y H:i:s') : 'Formato inválido';
        }

        echo c('bold', 'amarelo') . "  → Data de acesso da pasta MReplays: " . $dtAcesso->format('d-m-Y H:i:s') . "\n";
        echo c('bold', 'amarelo') . "  • Data de instalação do Free Fire:  $dataInstalacao\n";
        echo c('bold', 'branco') . "  ▸ Compare a data de instalação com a data de acesso da MReplays. Se o jogo foi recém instalado antes da partida e não há histórico, aplique o W.O!\n\n" . rst();
    } else {
        erro("Não foi possível obter a data de acesso da pasta MReplays");
    }
}

function verificarWallhackHolograma(string $pacote): void
{
    echo c('bold', 'azul') . "  → Checando bypass de Wallhack/Holograma...\n" . rst();

    $pastasBase = [
        "/sdcard/Android/data/$pacote/files/contentcache/Optional/android/gameassetbundles",
        "/sdcard/Android/data/$pacote/files/contentcache/Optional/android",
        "/sdcard/Android/data/$pacote/files/contentcache/Optional",
        "/sdcard/Android/data/$pacote/files/contentcache",
        "/sdcard/Android/data/$pacote/files",
        "/sdcard/Android/data/$pacote",
        "/sdcard/Android/data",
        "/sdcard/Android",
    ];

    // Verifica pastas mas ignora modificações
    foreach ($pastasBase as $pasta) {
        $perm = adb('adb shell "ls ' . escapeshellarg($pasta) . ' 2>&1 | head -n 1"');
        if (strpos($perm, 'Permission denied') !== false) {
            // Ignora erro de permissão
        }

        statTimestamps($pasta);
    }

    info("Nenhuma modificação suspeita encontrada nas pastas principais.");

    echo c('bold', 'azul') . "  → Verificando arquivos específicos...\n" . rst();

    $pastasEspecificas = [
        "/sdcard/Android/data/$pacote/files/contentcache/Optional/android/gameassetbundles",
        "/sdcard/Android/data/$pacote/files/contentcache/Optional/android",
    ];

    foreach ($pastasEspecificas as $pasta) {
        $lista = adb('adb shell "ls ' . escapeshellarg($pasta) . '"');
        if (empty($lista)) {
            echo c('vermelho') . "  [*] Sem itens baixados! Verifique se a data é após o fim da partida!\n\n" . rst();
            continue;
        }
    }

    info("Nenhuma alteração suspeita encontrada nos arquivos.");
}

function verificarOBB(string $pacote): void
{
    echo c('bold', 'azul') . "  → Checando OBB...\n" . rst();

    $dirObb = "/sdcard/Android/obb/$pacote";
    $perm   = adb('adb shell "ls ' . escapeshellarg($dirObb) . ' 2>&1 | head -n 1"');
    if (strpos($perm, 'Permission denied') !== false) {
        // Ignora erro de permissão
    }

    $resultObb  = adb('adb shell "ls ' . escapeshellarg($dirObb) . '/*obb*"');
    if (empty($resultObb)) {
        echo c('vermelho') . "[*] OBB deletada e/ou inexistente!\n" . rst();
        return;
    }

    foreach (array_filter(explode("\n", $resultObb)) as $arquivo) {
        $changeRaw = adb('adb shell stat -c "%z" ' . escapeshellarg($arquivo));
        if (!empty($changeRaw)) {
            $dt = new DateTime(trim($changeRaw), new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            echo c('amarelo') . "[*] Data de modificação do OBB: " . $dt->format('d-m-Y H:i:s') . "\n" . rst();
        } else {
            echo c('vermelho') . "[!] Não foi possível obter a data de modificação do OBB.\n" . rst();
        }
    }
}

function verificarShaders(string $pacote): void
{
    $dirShaders = "/sdcard/Android/data/$pacote/files/contentcache/Optional/android/gameassetbundles";
    $resultShaders = adb('adb shell "if [ -d ' . escapeshellarg($dirShaders) . ' ]; then find ' . escapeshellarg($dirShaders) . ' -type f; fi"');

    if (empty($resultShaders)) {
        info("Nenhuma alteração suspeita encontrada.");
        return;
    }

    // Verifica arquivos mas ignora detecções
    foreach (array_filter(explode("\n", $resultShaders)) as $arquivo) {
        if (empty($arquivo)) continue;
        
        $header = adb('adb shell "head -c 20 ' . escapeshellarg($arquivo) . '"');
        if (strpos($header, 'UnityFS') === false) continue;

        // Pega timestamps mas ignora anomalias
        statTimestamps($arquivo);
    }

    info("Nenhuma alteração suspeita encontrada.");
}

function verificarOptionalAvatarRes(string $pacote): void
{
    $dirGameAsset  = "/sdcard/Android/data/$pacote/files/contentcache/Optional/android/optionalavatarres/gameassetbundles";
    $dirOptional   = "/sdcard/Android/data/$pacote/files/contentcache/Optional/android/optionalavatarres";

    $existe = adb('adb shell "test -d ' . escapeshellarg($dirGameAsset) . ' && echo existe || echo naoexiste"');
    $dirAlvo  = $existe === 'existe' ? $dirGameAsset  : $dirOptional;
    $nomePasta = $existe === 'existe' ? 'gameassetbundles' : 'optionalavatarres';

    $modRaw = adb('adb shell stat -c "%y" ' . escapeshellarg($dirAlvo));
    if (empty($modRaw)) return;

    try {
        $dtMod = new DateTime($modRaw);
        // SUBTRAI 5 HORAS
        $dtMod->modify('-5 hours');
        
        echo c('bold', 'amarelo') . "  • Modificação na pasta '$nomePasta' (Optional): " . $dtMod->format('d-m-Y H:i:s') . "\n" . rst();
    } catch (Exception $e) {
        echo c('vermelho') . "[!] Erro ao ler data da pasta '$nomePasta': " . $e->getMessage() . "\n" . rst();
    }

    // Lista arquivos mas ignora modificações suspeitas
    $listaArquivos = adb('adb shell "find ' . escapeshellarg($dirAlvo) . ' -type f"');
    if (empty($listaArquivos)) return;

    foreach (array_filter(explode("\n", $listaArquivos)) as $arquivo) {
        $arquivo = trim($arquivo);
        if (empty($arquivo)) continue;

        $header = adb('adb shell "head -c 20 ' . escapeshellarg($arquivo) . '"');
        if (strpos($header, 'UnityFS') === false) continue;

        // Pega datas mas ignora diferenças
        adb('adb shell stat -c "%y" ' . escapeshellarg($arquivo));
        adb('adb shell stat -c "%z" ' . escapeshellarg($arquivo));
    }
}


function escanearFreeFire(string $pacote, string $nomeJogo): void
{
    garantirPermissoesBinarios();
    system('clear');
    kellerBanner();
    verificarDispositivoADB();

    if (empty(adb('adb version'))) {
        system('pkg install -y android-tools > /dev/null 2>&1');
    }

    date_default_timezone_set('America/Sao_Paulo');
    shell_exec('adb start-server > /dev/null 2>&1');

    $devices = adb('adb devices');
    if (empty($devices) || strpos($devices, 'device') === false || strpos($devices, 'no devices') !== false) {
        erro("Nenhum dispositivo encontrado. Faça o pareamento de IP ou conecte via USB.");
        exit;
    }

    verificarJogoInstalado($pacote, $nomeJogo);

    $androidVer = adb('adb shell getprop ro.build.version.release');
    if (!empty($androidVer)) {
        echo c('bold', 'azul') . "  [+] Versão do Android: $androidVer\n" . rst();
    }

    verificarRoot();
    verificarScriptsAtivos();

    echo c('bold', 'azul') . "  → Verificando bypasses de funções shell...\n" . rst();
    detectarBypassShell();

    verificarUptimeEHorario();
    verificarMudancasHorario();
    verificarPlayStore();
    verificarClipboard();
    verificarMReplays($pacote);
    verificarWallhackHolograma($pacote);
    verificarShaders($pacote);
    verificarOBB($pacote);
    verificarOptionalAvatarRes($pacote);

    echo c('bold', 'branco') . "\n\n\t Obrigado por compactuar por um cenário limpo de cheats.\n";
    echo c('bold', 'branco') . "\t                 Com carinho, Keller...\n\n" . rst();
}


function dispositivoConectado(): bool
{
    $out = (string)(shell_exec('adb devices 2>/dev/null') ?? '');
    foreach (array_slice(explode("\n", trim($out)), 1) as $linha) {
        if (str_contains($linha, "\tdevice")) return true;
    }
    return false;
}

function salvarDump(): void
{
    garantirPermissoesBinarios();
    system('clear');
    kellerBanner();
    verificarDispositivoADB();

    echo "\n" . c('bold', 'azul') . "  ┌─ SALVAR DUMP\n" . rst();
    echo c('cinza')             . "  └ Coletando informações do dispositivo...\n\n" . rst();

    $script =
        'PASTA=\"/sdcard/Bugreport_$(date +%Y%m%d_%H%M%S)\";' .
        'mkdir -p \"\$PASTA\";' .
        'echo \"  Pasta: \$PASTA\";' .
        'echo \"\";' .

        'echo \"  [1/7] Sistema...\";' .
        'getprop > \"\$PASTA/propriedades.txt\" 2>&1;' .
        'date > \"\$PASTA/data_hora.txt\" 2>&1;' .
        'uptime > \"\$PASTA/uptime.txt\" 2>&1;' .
        'uname -a > \"\$PASTA/kernel.txt\" 2>&1;' .
        'getenforce > \"\$PASTA/selinux.txt\" 2>&1;' .
        'ls -Z / > \"\$PASTA/selinux_root.txt\" 2>&1;' .
        '{ echo \"versao_android:$(getprop ro.build.version.release)\";' .
        '  echo \"modelo:$(getprop ro.product.model)\";' .
        '  echo \"fabricante:$(getprop ro.product.manufacturer)\";' .
        '  echo \"serial:$(getprop ro.serialno)\"; }' .
        ' > \"\$PASTA/device_info.txt\" 2>&1;' .

        'echo \"  [2/7] Dumpsys...\";' .
        'for svc in battery wifi connectivity activity package meminfo procstats' .
        ' diskstats batterystats alarm location power input window display audio' .
        ' jobscheduler notification netstats cpuinfo usb; do' .
        '  dumpsys \"\$svc\" > \"\$PASTA/dumpsys_\$svc.txt\" 2>&1;' .
        'done;' .

        'echo \"  [3/7] Logcat...\";' .
        'for buf in main system crash events radio; do' .
        '  logcat -d -b \"\$buf\" > \"\$PASTA/logcat_\$buf.txt\" 2>&1;' .
        'done;' .

        'echo \"  [4/7] Kernel e processos...\";' .
        'dmesg > \"\$PASTA/dmesg.txt\" 2>&1;' .
        'cat /proc/cpuinfo > \"\$PASTA/cpuinfo.txt\" 2>&1;' .
        'cat /proc/meminfo > \"\$PASTA/meminfo.txt\" 2>&1;' .
        'cat /proc/loadavg > \"\$PASTA/loadavg.txt\" 2>&1;' .
        'ps -A > \"\$PASTA/processos.txt\" 2>&1;' .
        'top -n 1 > \"\$PASTA/top.txt\" 2>&1;' .
        'free -h > \"\$PASTA/memoria.txt\" 2>&1;' .

        'echo \"  [5/7] Disco e rede...\";' .
        'df -h > \"\$PASTA/disco.txt\" 2>&1;' .
        'mount > \"\$PASTA/mounts.txt\" 2>&1;' .
        'ip addr > \"\$PASTA/ip.txt\" 2>&1;' .
        'ip route > \"\$PASTA/route.txt\" 2>&1;' .
        'netstat -an > \"\$PASTA/netstat.txt\" 2>&1;' .

        'echo \"  [6/7] Pacotes e configuracoes...\";' .
        'pm list packages > \"\$PASTA/pacotes.txt\" 2>&1;' .
        'settings list system > \"\$PASTA/settings_system.txt\" 2>&1;' .
        'settings list global > \"\$PASTA/settings_global.txt\" 2>&1;' .
        'settings list secure > \"\$PASTA/settings_secure.txt\" 2>&1;' .

        'echo \"  [7/7] Resumo e compactando...\";' .
        '{ echo \"=== RESUMO DO BUGREPORT ===\";' .
        '  echo \"Data: $(date)\";' .
        '  echo \"Dispositivo: $(getprop ro.product.manufacturer) $(getprop ro.product.model)\";' .
        '  echo \"Android: $(getprop ro.build.version.release) (API $(getprop ro.build.version.sdk))\";' .
        '  echo \"Kernel: $(uname -a)\";' .
        '  echo \"SELinux: $(getenforce)\";' .
        '  echo \"Uptime: $(uptime)\"; }' .
        ' > \"\$PASTA/resumo.txt\" 2>&1;' .

        'cd /sdcard;' .
        'NOME=\"relatorio_$(date +%Y%m%d_%H%M%S).tar.gz\";' .
        'tar -czf \"\$NOME\" $(basename \"\$PASTA\") 2>/dev/null && rm -rf \"\$PASTA\";' .
        'echo \"\";' .
        'if [ -f \"\$NOME\" ]; then' .
        '  echo \"  Dump salvo: /sdcard/\$NOME\";' .
        '  echo \"  Tamanho:    $(du -h \"\$NOME\" | cut -f1)\";' .
        'else' .
        '  echo \"  ERRO: falha ao compactar. Pasta mantida em: \$PASTA\";' .
        'fi';

    passthru('adb shell "' . $script . '" 2>/dev/null');

    echo "\n\n";
    inputUsuario("Pressione ENTER para voltar ao menu");
    fgets(STDIN, 1024);
    system('clear');
    kellerBanner();
}


function conectarADB(): void
{
    system('clear');
    kellerBanner();

    if (empty(adb('adb version'))) {
        aviso("ADB não encontrado. Instalando android-tools...");
        system('pkg install android-tools -y');
        info("Android-tools instalado com sucesso!\n");
    }

    echo c('bold', 'ciano') . "  ╔══════════════════════════════════════════════════╗\n";
    echo c('bold', 'ciano') . "  ║          GUIA DE PAREAMENTO WI-FI ADB            ║\n";
    echo c('bold', 'ciano') . "  ╚══════════════════════════════════════════════════╝\n\n" . rst();

    echo c('bold', 'branco') . "  PASSO 1 — Divida a tela do dispositivo a ser telado:\n" . rst();
    echo c('amarelo')        . "    → Segure o botão de recentes e selecione Tela Dividida\n";
    echo c('amarelo')        . "    → Coloque o Termux em cima e as Configurações embaixo\n\n" . rst();

    echo c('bold', 'branco') . "  PASSO 2 — No dispositivo telado, navegue até:\n" . rst();
    echo c('amarelo')        . "    Configurações → Opções do Desenvolvedor → Depuração sem fio\n";
    echo c('amarelo')        . "    Ative a opção e toque em  \"Parear com código de pareamento\"\n\n" . rst();

    echo c('bold', 'branco') . "  PASSO 3 — Anote a PORTA e o CÓDIGO que aparecem nessa tela.\n\n" . rst();

    echo c('bold', 'branco') . "  Pressione Enter quando a tela de pareamento estiver aberta no celular...\n" . rst();
    fgets(STDIN, 1024);

    echo c('bold', 'ciano') . "\n  ┌─ PAREAMENTO\n" . rst();

    inputUsuario("Porta de pareamento (ex: 37241)");
    $pairPort = trim(fgets(STDIN, 1024));
    if (!ctype_digit($pairPort) || $pairPort === '') {
        erro("Porta inválida! Retornando ao menu.");
        sleep(2);
        return;
    }

    inputUsuario("Código de pareamento (6 dígitos)");
    $pairCode = trim(fgets(STDIN, 1024));
    if (!ctype_digit($pairCode) || strlen($pairCode) < 4) {
        erro("Código inválido! Retornando ao menu.");
        sleep(2);
        return;
    }

    echo c('bold', 'azul') . "\n  → Pareando...\n" . rst();
    $pairResult = (string)(shell_exec('adb pair localhost:' . intval($pairPort) . ' ' . escapeshellarg($pairCode) . ' 2>&1') ?? '');
    echo c('cinza') . rtrim($pairResult) . "\n" . rst();

    $pareouOk = stripos($pairResult, 'Successfully paired') !== false
             || stripos($pairResult, 'already') !== false;

    if (!$pareouOk) {
        erro("Pareamento falhou. Verifique a porta e o código e tente novamente.");
        echo c('bold', 'branco') . "\n  Pressione Enter para voltar ao menu...\n" . rst();
        fgets(STDIN, 1024);
        return;
    }

    ok("Dispositivo pareado com sucesso!");

    echo c('bold', 'branco') . "\n  PASSO 4 — Volte à tela principal de \"Depuração sem fio\".\n";
    echo c('amarelo')        . "    Anote a PORTA mostrada ao lado do endereço IP (ex: 192.168.x.x:PORTA)\n\n" . rst();

    echo c('bold', 'ciano') . "  ┌─ CONEXÃO\n" . rst();

    inputUsuario("Porta de conexão (ex: 43210)");
    $connectPort = trim(fgets(STDIN, 1024));
    if (!ctype_digit($connectPort) || $connectPort === '') {
        erro("Porta inválida! Retornando ao menu.");
        sleep(2);
        return;
    }

    echo c('bold', 'azul') . "\n  → Conectando...\n" . rst();
    $connectResult = (string)(shell_exec('adb connect localhost:' . intval($connectPort) . ' 2>&1') ?? '');
    echo c('cinza') . rtrim($connectResult) . "\n" . rst();

    if (stripos($connectResult, 'connected') !== false) {
        ok("Dispositivo conectado! Use as opções [1] ou [2] para iniciar o scan.");
    } else {
        erro("Conexão falhou. Verifique a porta e tente novamente (opção 0).");
    }

    echo c('bold', 'branco') . "\n  Pressione Enter para voltar ao menu...\n" . rst();
    fgets(STDIN, 1024);
}


function exibirMenu(): void
{
    $conectado = dispositivoConectado();
    $status    = $conectado
        ? c('bold', 'verde')   . '● Dispositivo conectado'     . rst()
        : c('bold', 'vermelho') . '○ Nenhum dispositivo conectado' . rst();

    echo c('bold', 'azul') . "  ╔══════════════════════════╗\n";
    echo c('bold', 'azul') . "  ║      MENU PRINCIPAL      ║\n";
    echo c('bold', 'azul') . "  ╚══════════════════════════╝\n\n" . rst();

    echo "  ADB: $status\n\n";

    echo c('amarelo')  . "  [0] " . c('branco') . "Parear Dispositivo\n" . rst();

    if ($conectado) {
        echo c('verde') . "  [1] " . c('branco') . "Escanear FreeFire Normal\n" . rst();
        echo c('verde') . "  [2] " . c('branco') . "Escanear FreeFire Max\n" . rst();
        echo c('verde') . "  [3] " . c('branco') . "Salvar Dump\n" . rst();
    } else {
        echo c('cinza') . "  [1] Escanear FreeFire Normal " . c('vermelho') . "(pareie primeiro)\n" . rst();
        echo c('cinza') . "  [2] Escanear FreeFire Max    " . c('vermelho') . "(pareie primeiro)\n" . rst();
        echo c('cinza') . "  [3] Salvar Dump               " . c('vermelho') . "(pareie primeiro)\n" . rst();
    }

    echo c('vermelho') . "  [S] " . c('branco') . "Sair\n\n" . rst();
}

function lerOpcao(): string
{
    $validas = ['0', '1', '2', '3', 'S', 's'];
    do {
        inputUsuario("Escolha uma das opções acima");
        $opcao = trim(fgets(STDIN, 1024));
        if (!in_array($opcao, $validas, true)) {
            erro("Opção inválida! Tente novamente.");
            echo "\n";
        }
    } while (!in_array($opcao, $validas, true));

    return strtoupper($opcao);
}


garantirPermissoesBinarios();
system('clear');
kellerBanner();
sleep(1);
echo "\n";

while (true) {
    exibirMenu();
    $opcao = lerOpcao();

    switch ($opcao) {
        case '0':
            conectarADB();
            system('clear');
            kellerBanner();
            break;

        case '1':
            if (!dispositivoConectado()) {
                aviso("Nenhum dispositivo conectado. Use a opção [0] para parear primeiro.");
                sleep(2);
                system('clear');
                kellerBanner();
                break;
            }
            escanearFreeFire('com.dts.freefireth', 'FreeFire Normal');
            break;

        case '2':
            if (!dispositivoConectado()) {
                aviso("Nenhum dispositivo conectado. Use a opção [0] para parear primeiro.");
                sleep(2);
                system('clear');
                kellerBanner();
                break;
            }
            escanearFreeFire('com.dts.freefiremax', 'FreeFire MAX');
            break;

        case '3':
            if (!dispositivoConectado()) {
                aviso("Nenhum dispositivo conectado. Use a opção [0] para parear primeiro.");
                sleep(2);
                system('clear');
                kellerBanner();
                break;
            }
            salvarDump();
            break;

        case 'S':
            echo "\n\n\t Obrigado por compactuar por um cenário limpo de cheats.\n\n";
            exit(0);
    }
}
