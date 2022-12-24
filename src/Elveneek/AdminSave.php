<?php

namespace Elveneek;
use \Elveneek\ActiveRecord;
class AdminSave extends Service
{
	//Сохранение данных которые пришли из формы
	public static function run($data)
	{
		$data = $data['data'];
		//todo: $data['products']['new_123234'] и $data['products'][12]['category_id']='new_23444';
		foreach ($data as $table=>$toSave){
			foreach ($toSave as $id => $fields){
				//СОздаем объект Activerecord_safe для собстна сохранения
				$object = ActiveRecord::fromTable($table, '_safe')->_findOne($id);
				foreach($fields as $field=>$value){
					$object->{$field} = $value;
				}
				$object->save();
			}
		}
	}
}
