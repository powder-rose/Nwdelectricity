<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/power-tools.php';
$copy=nwdPowerToolsCopy();$fields=nwdPowerToolsFields($result['rows'] ?? array());
$escape=function ($v) { return htmlspecialchars((string)$v,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8'); };
if (count($fields)!==4 || (int)$result['site_id']!==27 || !empty($result['text'])) {
    echo '<p role="alert">'.$escape($copy['errors']['failure']).'</p>';return;
}
$instance=uniqid('fn_');
?>
<form method="post" action="" class="fn-form ajax" novalidate data-fn-form>
<input type="hidden" name="module" value="feedback"><input type="hidden" name="action" value="add">
<input type="hidden" name="site_id" value="27"><input type="hidden" name="form_tag" value="<?= $escape($result['form_tag']) ?>">
<input type="hidden" name="add_c" value="4l"><input type="hidden" name="nwd_service" value="power-tools">
<input type="hidden" name="nwd_request_id" value="<?= bin2hex(random_bytes(16)) ?>"><input type="hidden" name="nwd_attribution" value="{}">
<input type="hidden" name="tmpcode" value="<?= bin2hex(random_bytes(16)) ?>">
<?php foreach (array('name','phone','email','textarea') as $index=>$type) {
    $param=$fields[$type];$field=$copy['fields'][$index];$id=$instance.'_p'.(int)$param['id'];$key='p'.(int)$param['id'];
    $attrs=' id="'.$id.'" name="'.$key.'" data-fn-field="'.$type.'" aria-describedby="'.$id.'_error" placeholder="'.$escape($field[3]).'"';
    echo '<div class="fn-form__field fn-form__field--'.$type.'"><label for="'.$id.'">'.$escape($field[0]).($type==='phone' ? ' <span aria-hidden="true">*</span>' : '').'</label>';
    if ($type==='textarea') { echo '<textarea rows="3" maxlength="4000"'.$attrs.'></textarea>'; }
    else { echo '<input type="'.($type==='name' ? 'text' : ($type==='phone' ? 'tel' : 'email')).'" autocomplete="'.($type==='name' ? 'name' : ($type==='phone' ? 'tel' : 'email')).'" maxlength="'.($type==='name' ? 100 : ($type==='phone' ? 40 : 254)).'"'.($type==='phone' ? ' required inputmode="tel"' : '').$attrs.'>'; }
    echo '<span id="'.$id.'_error" class="errors error_'.$key.'" role="alert" style="display:none"></span></div>';
} ?>
<?php
// The current native model uses p_name as a hidden trap, rather than a CAPTCHA.
// Render our accessible-off trap once; preserve a genuine CAPTCHA if one is configured later.
if (!empty($result['captcha']) && !preg_match('/name=["\']p_name["\']/',$result['captcha'])) { echo $result['captcha']; }
?>
<button type="submit" class="fn-button"><?= $escape($copy['submit']) ?><span aria-hidden="true">↗</span></button>
<div class="fn-form__consent"><input id="<?= $instance ?>_consent" type="checkbox" name="nwd_consent" value="1" required aria-describedby="<?= $instance ?>_consent_error"><label for="<?= $instance ?>_consent">Я даю согласие на обработку персональных данных в соответствии с <a href="/privacy/" target="_blank" rel="noopener">политикой конфиденциальности</a></label></div>
<span id="<?= $instance ?>_consent_error" class="errors error_nwd_consent" role="alert" style="display:none"></span>
<div class="error" role="alert" style="display:none"></div><div data-lead-error aria-live="polite"></div>
<div class="fn-form__trap" aria-hidden="true"><label>Имя<input type="text" name="p_name" tabindex="-1" autocomplete="off"></label></div>
</form>
