<?php if (!defined('DIAFAN')) { http_response_code(404); exit; } ?>
<!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Новая заявка</title></head>
<body style="margin:0;padding:28px 12px;background:#f3f1eb;color:#171815;font-family:Arial,Helvetica,sans-serif">
<div style="display:none;max-height:0;overflow:hidden">Новая заявка №<?= (int)$lead['id'] ?> · <?= nwdFeedbackEscape($lead['form']) ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px">
<tr><td style="background:#141512;padding:32px;color:#fff">
<p style="margin:0 0 28px;color:#adf247;font-size:13px;letter-spacing:1px;font-weight:700">СПЕЦЭНЕРГОРАЗВИТИЕ</p>
<h1 style="margin:0 0 12px;font-size:32px;line-height:38px;font-weight:500">Новая заявка <span style="color:#adf247">№<?= (int)$lead['id'] ?></span></h1>
<p style="margin:0 0 18px;font-size:17px;line-height:26px"><?= nwdFeedbackEscape($lead['form']) ?></p>
<p style="margin:0;color:#bfc2b8;font-size:13px"><?= nwdFeedbackEscape($date) ?></p>
</td></tr>
<tr><td style="background:#fff;padding:20px 32px 28px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><?= $rows ?></table>
</td></tr>
<tr><td style="padding:26px 32px;background:#eae9e2;font-size:13px;line-height:22px">
<p style="margin:0 0 12px;color:#64665e">Страница обращения<br>
<a href="<?= nwdFeedbackEscape($lead['url']) ?>" style="color:#171815"><?= nwdFeedbackEscape($lead['url']) ?></a></p>
<p style="margin:0 0 24px;color:#64665e">Клиент подтвердил согласие на обработку персональных данных.</p>
<table role="presentation" cellpadding="0" cellspacing="0"><tr><td bgcolor="#adf247" style="padding:14px 20px"><a href="https://nwdelectricity.ru/redaktsiya/index.php?tab=leads" style="color:#141512;font-size:14px;text-decoration:none">Открыть заявки в редакции &#8599;&#xfe0e;</a></td></tr></table>
</td></tr>
</table></td></tr></table>
</body></html>
