<?php
	use Bitrix\Main\Application;
	class FileNameFixModule
	{
		public function OnPageStart()
		{
			if (preg_match("~/bitrix/admin/iblock_element_edit\.php~", $_SERVER['REQUEST_URI']))
			{
				global $PROP;
				foreach($PROP as $main_key => &$property)
					foreach($property as $key => &$prop_value)
						if (preg_match("~^n[0-9]+$~", $key) && is_array($prop_value) && !empty($prop_value['type']) && preg_match("~image~", $prop_value['type']))
						{
							$res = CFile::GetList(array(), array("ORIGINAL_NAME" => $prop_value['name']));
							$file_on_server_array = $res->GetNext();
							
							if (!empty($file_on_server_array))
							{
								$file_name = explode(".", $prop_value['name']);
								$rashirenie = $file_name[count($file_name) - 1];
								unset($file_name[count($file_name) - 1]);
								
								$new_name = implode(".", $file_name);
								$new_name .= "_".date("dmYhis").".".$rashirenie;
								
								$prop_value['name'] = $new_name;
								$_POST['PROP'][$main_key][$key]['name'] = $new_name;
							}
						}
			}
		}
	}
?>