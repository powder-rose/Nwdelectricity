<?php
/**
 * Обработка POST-запроса
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

class Feedback_action extends Action
{	
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function add()
	{
        if (!isset($_POST['nwd_consent']) || $_POST['nwd_consent'] !== '1') {
            $this->result["errors"]["nwd_consent"] = 'Подтвердите согласие на обработку персональных данных.';
            return;
        }
        $requestId = $_POST['nwd_request_id'] ?? '';
        if (!is_string($requestId) || !preg_match('/^[a-f0-9]{32}$/D', $requestId)) {
            $this->result["errors"][0] = 'Обновите страницу и отправьте заявку ещё раз.';
            return;
        }

        if(!empty($_POST["p_name"]))
        {
            return;
        }
        $new_param = $this->diafan->filter($_POST, "string", "add_c");
        if(empty($new_param) || $new_param != '4l')
        {
            return;
        }
		$this->check_site_id();

		if ($this->result())
			return;

		if ($this->diafan->_captcha->configmodules('feedback', $this->site_id))
		{
			$this->check_captcha();
		}
		$params = $this->model->get_params(array("module" => "feedback", "where" => "site_id=".$this->site_id));
        $documentContext=null;
        if (isset($_POST['nwd_document_request'])) {
            if ($_POST['nwd_document_request']!=='1' || (int)$this->site_id!==27) {
                $this->result['errors'][0]='Неверная форма запроса документа.';return;
            }
            require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/document-request.php';
            $errors=array();
            try { $documentContext=nwdDocumentRequestValidate($params,$_POST,$errors); }
            catch (Throwable $error) { $errors[0]='Не удалось проверить документ. Обновите статью и попробуйте ещё раз.'; }
            if ($errors) { $this->result['errors']=$errors;return; }
        }
        $serviceContext=null;
        if (isset($_POST['nwd_service'])) {
            require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/phase-neutral.php';
            $serviceCopy=nwdPhaseNeutralCopy();
            if ($_POST['nwd_service']==='rcd') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/rcd.php';
                $serviceCopy=nwdRcdCopy();
            }
            if ($_POST['nwd_service']==='power-tools') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/power-tools.php';
                $serviceCopy=nwdPowerToolsCopy();
            }
            if ($_POST['nwd_service']==='laboratory') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/laboratory.php';
                $serviceCopy=nwdLaboratoryCopy();
            }
            if ($_POST['nwd_service']==='insulation') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/insulation.php';
                $serviceCopy=nwdInsulationCopy();
            }
            if ($_POST['nwd_service']==='grounding') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/grounding.php';
                $serviceCopy=nwdGroundingCopy();
            }
            if ($_POST['nwd_service']==='breakers') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/breakers.php';
                $serviceCopy=nwdBreakersCopy();
            }
            if ($_POST['nwd_service']==='cables') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/cables.php';
                $serviceCopy=nwdCablesCopy();
            }
            if ($_POST['nwd_service']==='commissioning') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/commissioning.php';
                $serviceCopy=nwdCommissioningCopy();
            }
            if ($_POST['nwd_service']==='tehotchet') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/tehotchet.php';
                $serviceCopy=nwdTehotchetCopy();
            }
            if ($_POST['nwd_service']==='continuity') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/continuity.php';
                $serviceCopy=nwdContinuityCopy();
            }
            if ($_POST['nwd_service']==='lightning') {
                require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/lightning.php';
                $serviceCopy=nwdLightningCopy();
            }
            $errors=array();
            if (!in_array($_POST['nwd_service'],array('phase-neutral','rcd','power-tools','laboratory','insulation','grounding','breakers','cables','commissioning','tehotchet','continuity','lightning'),true) || (int)$this->site_id!==27 || $documentContext) {
                $errors[0]=$serviceCopy['errors']['failure'];
            } else {
                try {
                    if ($_POST['nwd_service']==='lightning') { $serviceContext=nwdLightningValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='continuity') { $serviceContext=nwdContinuityValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='tehotchet') { $serviceContext=nwdTehotchetValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='commissioning') { $serviceContext=nwdCommissioningValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='cables') { $serviceContext=nwdCablesValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='breakers') { $serviceContext=nwdBreakersValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='grounding') { $serviceContext=nwdGroundingValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='insulation') { $serviceContext=nwdInsulationValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='laboratory') { $serviceContext=nwdLaboratoryValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='power-tools') { $serviceContext=nwdPowerToolsValidate($params,$_POST,$errors); }
                    elseif ($_POST['nwd_service']==='rcd') { $serviceContext=nwdRcdValidate($params,$_POST,$errors); }
                    else { $serviceContext=nwdPhaseNeutralValidate($params,$_POST,$errors); }
                }
                catch (Throwable $error) { $errors[0]=$serviceCopy['errors']['failure']; }
            }
            if ($errors) { $this->result['errors']=$errors;return; }
        }
        if (!$serviceContext) { $this->empty_required_field(array("params" => $params)); }


		if ($this->result())
			return;

        require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/lead.php';
        $source=$serviceContext ? $serviceContext['source'] : ($documentContext ? $documentContext['source'] : nwdFeedbackSource());
        $raw = array('site_id' => $this->site_id);
        if ($documentContext) { $raw['document_source']=$source; }
        if ($serviceContext) { $raw['service_source']=$source; }
        foreach ($params as $param) {
            $key = 'p'.$param['id'];
            $raw[$key] = $_POST[$key] ?? '';
        }
        $digest = hash('sha256', serialize($raw));
        $mailId = null;
        $repeated = false;
        try {
            DB::query('START TRANSACTION');
            DB::query("INSERT IGNORE INTO {nwd_feedback_request} (request_id,digest,created) VALUES ('%s','%s',%d)", $requestId, $digest, time());
            $receipt = DB::query_fetch_array("SELECT digest,feedback_id,mail_id FROM {nwd_feedback_request} WHERE request_id='%s' LIMIT 1 FOR UPDATE", $requestId);
            if (!$receipt || !hash_equals($receipt['digest'], $digest)) {
                throw new RuntimeException('Request data changed');
            }
            if ($receipt['feedback_id']) {
                $repeated = true;
            } else {
                $save = DB::query("INSERT INTO {feedback} (created,site_id,lang_id,url,user_id) VALUES (%d,%d,%d,'%h',%d)",
                    time(), $this->site_id, _LANG, $source, $this->diafan->_users->id);
                if (!empty($_POST["tmpcode"])) {
                    DB::query("UPDATE {images} SET element_id=%d,tmpcode='' WHERE module_name='feedback' AND element_id=0 AND tmpcode='%s'", $save, $_POST["tmpcode"]);
                }
                $this->insert_values(array("id"=>$save,"table"=>"feedback","params"=>$params));
                if ($this->result()) { DB::query('ROLLBACK'); return; }
                $lead = nwdFeedbackLead($save, $this->site_id);
                $from = $this->diafan->configmodules("emailconf", 'feedback', $this->site_id)
                    ? $this->diafan->configmodules("email", 'feedback', $this->site_id) : EMAIL_CONFIG;
                $from = filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : 'office@nwdelectricity.ru';
                $mailId = $this->diafan->_postman->db_add(
                    'office@nwdelectricity.ru', 'Новая заявка №'.$save.' · '.$lead['form'],
                    nwdFeedbackEmail($lead), $from, 'mail', true
                );
                if (!$mailId) { throw new RuntimeException('Mail queue unavailable'); }
                DB::query("UPDATE {nwd_feedback_request} SET feedback_id=%d,mail_id='%s' WHERE request_id='%s'", $save, $mailId, $requestId);
            }
            // BOYKOV_HUB_ENQUEUE_V1
            require_once ABSOLUTE_PATH.'custom/energo/modules/feedback/boykov-hub.php';
            if ($repeated) { $lead = nwdFeedbackLead((int)$receipt['feedback_id'], $this->site_id); }
            nwdHubEnqueue($lead, $requestId);
            DB::query('COMMIT');
        } catch (Throwable $error) {
            DB::query('ROLLBACK');
            error_log('NWD feedback: could not save lead and mail notification ('.get_class($error).').');
            $this->result["errors"][0] = $serviceContext ? $serviceCopy['errors']['failure'] : 'Не удалось отправить заявку. Попробуйте ещё раз или позвоните нам.';
            return;
        }
        if (!$repeated) {
            // The durable mail queue retains the notification if delivery needs a retry.
            try { if (getenv('NWD_LOCAL') !== '1') { $this->diafan->_postman->message_send($mailId); } }
            catch (Throwable $error) { error_log('NWD feedback: mail notification remains in the delivery queue.'); }
            nwdFeedbackCrm($lead);
            $this->send_sms();
        }

		$mes = $serviceContext ? $serviceCopy['errors']['success'] : $this->diafan->configmodules('add_message', 'feedback', $this->site_id, _LANG);
		$this->result["errors"][0] = $mes ? $mes : ' ';
		$this->result["result"] = 'success';
		$this->result["data"] = array("form" => false);
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		if(empty($_POST["images_param_id"]))
		{
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");

		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload(0, "feedback", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $_POST["tmpcode"]);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', 0, "feedback", 'element', 0, '', $param_id, 0, '', $_POST["tmpcode"]);
			$this->result["data"] = $this->diafan->_tpl->get('images', "feedback", $images);
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if(empty($_POST["id"]))
		{
			return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='feedback' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 * 
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'feedback', $this->site_id))
			return;

		$message = $this->diafan->configmodules("sms_message_admin", 'feedback', $this->site_id);

		$to   = $this->diafan->configmodules("sms_admin", 'feedback', $this->site_id);

		$this->diafan->_postman->message_add_sms($message, $to);
	}
}
