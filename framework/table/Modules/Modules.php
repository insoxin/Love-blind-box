<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
namespace We7\Table\Modules;

class Modules extends \We7Table {
	protected $tableName = 'modules';
	protected $primaryKey = 'mid';
	protected $templateFields = array('mid', 'name', 'version', 'title', 'description', 'type', 'sections');
	protected $field = array(
		'name',
		'type',
		'title',
		'title_initial',
		'version',
		'ability',
		'description',
		'author',
		'url',
		'settings',
		'subscribes',
		'handles',
		'isrulefields',
		'issystem',
		'target',
		'iscard',
		'permissions',
		'wxapp_support',
		'account_support',
		'welcome_support',
		'webapp_support',
		'oauth_type',
		'phoneapp_support',
		'xzapp_support',
		'aliapp_support',
		'logo',
		'baiduapp_support',
		'toutiaoapp_support',
		'cloud_record',
		'sections',
		'application_type',
	);
	protected $default = array(
		'name' => '',
		'type' => '',
		'title' => '',
		'title_initial' => '',
		'version' => '',
		'ability' => '',
		'description' => '',
		'author' => '',
		'url' => '',
		'settings' => '0',
		'subscribes' => '',
		'handles' => '',
		'isrulefields' => '0',
		'issystem' => '0',
		'target' => '0',
		'iscard' => '0',
		'permissions' => '',
		'wxapp_support' => '1',
		'account_support' => '1',
		'welcome_support' => '1',
		'webapp_support' => '1',
		'oauth_type' => '1',
		'phoneapp_support' => '1',
		'xzapp_support' => '1',
		'aliapp_support' => '1',
		'logo' => '',
		'baiduapp_support' => '1',
		'toutiaoapp_support' => '1',
		'cloud_record' => 0,
		'sectinos' => 0,
		'application_type' => 1,
	);

	public function bindings() {
		return $this->hasMany('modules_bindings', 'module', 'name');
	}

	public function getByName($module_name) {
		$result = $this->query->where('name', $module_name)->get();
		if (!empty($result['subscribes'])) {
			$result['subscribes'] = iunserializer($result['subscribes']);
		}
		if (!empty($result['handles'])) {
			$result['handles'] = iunserializer($result['handles']);
		}
		return $result;
	}
		protected function templatesMidToId($result) {
		if (empty($result) || !is_array($result)) {
			return array();
		}
		foreach ($result as $key => $template) {
			$result[$key] = $this->templateMidToId($template);
		}
		return $result;
	}
	protected function templateMidToId($result) {
		global $_W;
		if (empty($result) || !is_array($result)) {
			return array();
		}
		$result['id'] = $result['mid'];
		if (file_exists('../app/themes/'.$result['name'].'/preview.jpg')) {
			$result['logo'] = $_W['siteroot'].'app/themes/'.$result['name'].'/preview.jpg';
		} else {
			$result['logo'] = $_W['siteroot'].'web/resource/images/nopic-203.png';
		}
		return $result;
	}
	public function searchTemplateWithName($module_name) {
		return $this->query->where('name', $module_name);
	}
	public function getAllTemplates($keyfields = '') {
		$fields = array('mid', 'name', 'version', 'title', 'description', 'type', 'sections');
		$result = $this->query->select($fields)->where(array('application_type' => APPLICATION_TYPE_TEMPLATES, 'account_support' => MODULE_SUPPORT_ACCOUNT))->orderby('mid', 'DESC')->getall($keyfields);
		return $this->templatesMidToId($result);
	}

	public function getTemplateByName($module_name) {
		$result = $this->query->select($this->templateFields)->where('name', $module_name)->where('application_type', APPLICATION_TYPE_TEMPLATES)->get();
		return $this->templateMidToId($result);
	}
	public function getTemplateByNames($module_names, $keyfields = '') {
		$result = $this->query->select($this->templateFields)->where('name', $module_names)->where('application_type', APPLICATION_TYPE_TEMPLATES)->getall($keyfields);
		return $this->templatesMidToId($result);
	}

	public function getTemplateById($id) {
		$result = $this->query->select($this->templateFields)->where('mid', $id)->where('application_type', APPLICATION_TYPE_TEMPLATES)->get();
		return $this->templateMidToId($result);
	}

	public function getAllTemplateByIds($ids, $keyfields = '') {
		$result = $this->query->select($this->templateFields)->where('mid', $ids)->where('application_type', APPLICATION_TYPE_TEMPLATES)->orderby('mid', 'DESC')->getall($keyfields);
		return $this->templatesMidToId($result);
	}

	public function getByNameList($modulename_list, $get_system = false) {
		$this->query->whereor('name', $modulename_list)->orderby('mid', 'desc');
		if (!empty($get_system)) {
			$this->whereor('issystem', 1);
		}
		return $this->query->getall('name');
	}

	public function deleteByName($module_name) {
		return $this->query->where('name', $module_name)->delete();
	}

	public function getByHasSubscribes() {
		return $this->query->select('name', 'subscribes')->where('subscribes !=', '')->getall();
	}

	public function getSupportWxappList() {
		return $this->query->where('wxapp_support', MODULE_SUPPORT_WXAPP)->getall('mid');
	}

	public function searchWithType($type, $method = '=') {
		if ($method == '=') {
			$this->query->where('type', $type);
		} else {
			$this->query->where('type <>', $type);
		}
		return $this;
	}

	public function getNonRecycleModules() {
		load()->model('module');
		$modules = $this->where('issystem' , 0)->orderby('mid', 'DESC')->getall('name');
		if (empty($modules)) {
			return array();
		}
		foreach ($modules as &$module) {
			$module_info = module_fetch($module['name']);
			if (empty($module_info)) {
				unset($module);
			}
			if (!empty($module_info['recycle_info'])) {
				foreach (module_support_type() as $support => $value) {
					if ($module_info['recycle_info'][$support] > 0 && $module_info[$support] == $value['support']) {
						$module[$support] = $value['not_support'];
					}
				}
			}
		}
		return $modules;
	}

	public function getInstalled() {
		load()->model('module');
		$fields = array_keys(module_support_type());
		$fields = array_merge(array('name', 'version', 'cloud_record'), $fields);
		return $this->query->select($fields)->where(array('issystem' => '0'))->getall('name');
	}
}