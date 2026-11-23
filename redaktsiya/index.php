<?php
define('NWD_PANEL',true);
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
panelHeaders();
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; object-src 'none'; base-uri 'none'; frame-ancestors 'self'; form-action 'self'");
$authorized=false;$error='';$csrf='';$tab='articles';$preview=isset($_GET['preview']);
try {
    panelDb(); panelSession();
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        panelCsrf($_POST['csrf'] ?? '');
        if (isset($_POST['logout'])) { panelLogout(); }
        else { panelLogin($_POST['password'] ?? ''); }
        header('Location: /redaktsiya/index.php',true,303); exit;
    }
    $authorized=panelAuthorized();$csrf=$_SESSION['csrf'];
    $tab=in_array($_GET['tab'] ?? '',array('leads','visits','plan'),true)?$_GET['tab']:'articles';
    session_write_close();
} catch (Throwable $e) { $error=panelErrorResponse($e);$csrf=$_SESSION['csrf'] ?? ''; }
?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="nwd-csrf" content="<?=panelHtml($csrf)?>">
<title><?= $authorized ? 'Панель редакции' : 'Вход в редакцию' ?> · Спецэнергоразвитие</title>
<link rel="stylesheet" href="/redaktsiya/assets/panel.css">
<?php if ($authorized && $tab==='visits'): ?><link rel="stylesheet" href="/redaktsiya/assets/statistics.css"><?php endif ?>
<?php if ($authorized && in_array($tab,array('articles','plan'),true)): ?><link rel="stylesheet" href="/redaktsiya/assets/editor.css?v=document-request-v1"><script type="module" src="/redaktsiya/assets/editor.js?v=document-request-v1"></script><?php endif ?>
<?php if ($authorized && $preview): ?><link rel="stylesheet" href="/redaktsiya/assets/reading.css?v=<?=filemtime(__DIR__.'/assets/reading.css')?>"><?php endif ?>
<link rel="icon" type="image/x-icon" sizes="16x16 24x24 32x32 48x48 64x64" href="/favicon.ico?v=51c83f25">
<link rel="icon" type="image/svg+xml" sizes="any" href="/favicon.svg">
</head><body>
<header class="panel-bar"><div><strong>СПЕЦЭНЕРГОРАЗВИТИЕ</strong><span>Редакция</span></div>
<?php if ($authorized): ?><form method="post"><input type="hidden" name="csrf" value="<?=panelHtml($csrf)?>"><button name="logout">Выйти</button></form><?php endif ?></header>
<?php if (!$authorized): ?><main class="login-wrap"><section class="login-card"><h1>Вход в редакцию</h1>
<form method="post"><label>Пароль<input type="password" name="password" autocomplete="current-password" required autofocus></label>
<input type="hidden" name="csrf" value="<?=panelHtml($csrf)?>"><button class="lime-button" type="submit">Войти <span aria-hidden="true">↗</span></button></form>
<?php if ($error): ?><p class="form-error" role="alert"><?=panelHtml($error)?></p><?php endif ?></section></main>
<?php else: ?>
<?php if (!$preview): ?><nav class="panel-nav" aria-label="Разделы панели"><?php foreach (array('articles'=>'Статьи','plan'=>'План публикаций','leads'=>'Заявки','visits'=>'Статистика') as $key=>$label): ?><a href="/redaktsiya/index.php?tab=<?=$key?>" <?=$tab===$key?'aria-current="page"':''?>><?=$label?></a><?php endforeach ?></nav><?php endif ?>
<main class="panel-main"><?php if (in_array($tab,array('articles','plan'),true)): ?><div id="editor-root"></div><noscript>Для редактора включите JavaScript.</noscript>
<?php else:
    require __DIR__.'/lib/reports.php';
    try {
        if ($tab === 'leads') {
            panelLeads();
        } elseif (($_GET['history'] ?? '') === 'diafan') {
            panelVisits();
        } else {
            require __DIR__.'/lib/statistics.php';
            panelShowStatistics($_GET['days'] ?? 30);
        }
    } catch (Throwable $e) { echo '<p role="alert">'.panelHtml(panelErrorResponse($e)).'</p>'; }
endif ?></main>
<?php endif ?></body></html>
