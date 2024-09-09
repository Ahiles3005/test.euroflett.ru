<?php
namespace Tools\Euroflett\Import;

class Elements
{
    public static function UpdateCheck(&$arFields) {
        if($_SESSION['IMPORT_UPDATE_HANDLER'] != 'Y') {
            $model = \CIBlockElement::GetProperty(
                $arFields['IBLOCK_ID'], 
                $arFields['ID'], 
                [], 
                ['CODE' => 'MODEL']
            ) -> GetNext();

            if(
                $arFields['PROPERTY_VALUES'][$model['ID']][$model['PROPERTY_VALUE_ID']]['VALUE'] != '' && 
                $model['VALUE'] != $arFields['PROPERTY_VALUES'][$model['ID']][$model['PROPERTY_VALUE_ID']]['VALUE']
            ) {
                $tableList = self::getTableList();
                $itemList = self::getItemList($tableList, $arFields['ID']);

                self::update($itemList, $arFields['PROPERTY_VALUES'][$model['ID']][$model['PROPERTY_VALUE_ID']]['VALUE']);
            }
        }
    }
    
    private static function getTableList() {
        global $DB;
        
        $tableRes = $DB -> Query("SHOW TABLES LIKE 'pricelist_sync_%';");

        $tableList = [];

        while($table = $tableRes -> Fetch()) {
            $tableList[] = array_shift($table);
        }
        
        return $tableList;
    }
    
    private static function getItemList($tableList, $id) {
        global $DB;
        
        $itemList = [];

        foreach($tableList as $table) {
            $itemRes = $DB -> Query("SELECT * FROM `" . $table . "` WHERE element_id='" . $id . "'");

            while($item = $itemRes -> Fetch()) {
                $itemList[$table][$item['element_id']][] = $item;
            }
        }
        
        return $itemList;
    }
    
    private static function update($itemList, $code) {
        global $DB;
        
        foreach($itemList as $tableName => $itemTableList) {
            foreach($itemTableList as $itemId => $item) {
                $DB -> Query("UPDATE " . $tableName . " SET code='" . $DB -> ForSql($code) . "' WHERE element_id='" . $item[0]['element_id'] . "';");
            }
        }
    }
}
