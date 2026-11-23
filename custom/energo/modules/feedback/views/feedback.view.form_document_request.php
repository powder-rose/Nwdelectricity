<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/document-request.php';
$fields=nwdDocumentRequestFields($result['rows'] ?? array());
if (count($fields)!==3 || !empty($result['text'])) { return; }
$escape=function($value) { return htmlspecialchars((string)$value,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8'); };
$instance=uniqid('document_request_');
?>
<div id="nwd-document-request" class="modal" aria-labelledby="nwd-document-request-heading">
  <button type="button" data-fancybox-close class="modal__close"><span class="visually-hidden">Закрыть окно</span><span class="modal__close-icon"></span></button>
  <p class="nwd-document-request__eyebrow">Материалы для вашей работы</p>
  <h2 id="nwd-document-request-heading" class="nwd-document-request__heading">Запросить документ<span aria-hidden="true">.</span></h2>
  <p class="nwd-document-request__title" data-document-request-title>Образец документа</p>
  <form method="POST" action="" class="ajax" data-document-request-form>
    <input type="hidden" name="module" value="feedback"><input type="hidden" name="action" value="add">
    <input type="hidden" name="form_tag" value="<?=$escape($result['form_tag'])?>"><input type="hidden" name="site_id" value="<?=(int)$result['site_id']?>">
    <input type="hidden" name="add_c" value="4l"><input type="hidden" name="nwd_request_id" value="<?=bin2hex(random_bytes(16))?>">
    <input type="hidden" name="nwd_document_request" value="1"><input type="hidden" name="nwd_document_article" value=""><input type="hidden" name="nwd_document_index" value="">
    <?php foreach (array('name'=>array('Имя','text','name',100),'phone'=>array('Телефон','tel','tel',40),'email'=>array('Почта','email','email',254)) as $type=>$settings): $id=(int)$fields[$type]['id']; ?>
    <label class="nwd-document-request__field" for="<?=$instance.$type?>"><?=$settings[0]?>
      <input class="nwd-document-request__input" id="<?=$instance.$type?>" name="p<?=$id?>" type="<?=$settings[1]?>" autocomplete="<?=$settings[2]?>" maxlength="<?=$settings[3]?>" <?php if ($type==='phone'): ?>inputmode="tel"<?php endif ?> required>
      <span class="errors error_p<?=$id?>" role="alert" style="display:none"></span>
    </label>
    <?php endforeach ?>
    <?php if (!empty($result['captcha'])) { echo $result['captcha']; } ?>
    <label class="nwd-document-request__consent" for="<?=$instance?>consent"><input id="<?=$instance?>consent" type="checkbox" name="nwd_consent" value="1" required><span>Я даю согласие на обработку персональных данных в соответствии с <a href="/privacy/" target="_blank" rel="noopener">политикой конфиденциальности</a>.</span></label>
    <p class="errors error_nwd_consent" role="alert" style="display:none"></p>
    <button type="submit" class="nwd-document-request__submit">Запросить документ <span aria-hidden="true">↗</span></button>
    <p class="errors error" role="alert" style="display:none"></p><p data-lead-error aria-live="polite"></p>
  </form>
</div>
