<?php

/*
Код предполагается использовать в init.php
*/

AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("CatalogException", "checkDate"));
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", Array("CatalogException", "checkShows"));

class CatalogException
{
	private static $iblockID = 2;

	private static $eventType = 'ADMIN_INFO';

  function checkDate(&$arFields)
  {
    if($arFields["IBLOCK_ID"] == self::$iblockID){
      $date_create = DateTime::createFromFormat('d.m.Y H:i:s', $arFields['ACTIVE_FROM']);
      $date_today = new DateTime();
      $date_diff = $date_create->diff($date_today)->format('%a');	

      if($date_diff < 7){
        global $APPLICATION;
        $APPLICATION->throwException("Товар {$arFields['NAME']} был создан менее одной недели назад и не может быть изменен");
        return false;
      }
    }
  }

	function checkShows($id)
  {
		$arFields = CIBlockElement::GetByID($id)->GetNext();

		if($arFields["IBLOCK_ID"] == self::$iblockID){
			$show_counter = $arFields["SHOW_COUNTER"];

			if($show_counter > 10000){
				global $APPLICATION, $USER;
				
				$arEventFields = array(
					'USER_LOGIN' => $USER->GetLogin(),
					'USER_ID' => $USER->GetID(),
					'PRODUCT_NAME' => $arFields['~NAME'],
					'PRODUCT_SHOWS' => $show_counter
				  );

				CEvent::Send(self::$eventType, SITE_ID, $arEventFields);
				
				$APPLICATION->throwException("Нельзя удалить данный товар, так как он очень популярный на сайте");
				return false;
			  }
		 }
   }
}

