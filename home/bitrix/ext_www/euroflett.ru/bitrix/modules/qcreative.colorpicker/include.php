<?php
  
IncludeModuleLangFile(__FILE__);

class UserDataColor extends CUserTypeString {

    function GetUserTypeDescription() {
        return array(
            "USER_TYPE_ID" => "color",
            "CLASS_NAME" => "UserDataColor",
            "DESCRIPTION" => GetMessage('COLORPICKER_PROPERTY_NAME'),
            "BASE_TYPE" => "int",
        );
    }

    function GetIBlockPropertyDescription() {
        return array(
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "color",
            "DESCRIPTION" => GetMessage('COLORPICKER_PROPERTY_NAME'),
            'GetPropertyFieldHtml' => array('UserDataColor', 'GetPropertyFieldHtml'),
            'GetAdminListViewHTML' => array('UserDataColor', 'GetAdminListViewHTML'),
        );
    }

    function getViewHTML($name, $value) {
        return '<div style="display: block; border: 1px solid;
                            border-radius: 25px;
                            display: block;
                            height: 30px;
                            width: 30px; background-color: #' . $value . '">&nbsp;</div>';
    }

 
    function getEditHTML($name, $value, $is_ajax = false) {
        $pathToLib = "/bitrix/js/qcreative.colorpicker/";  
        $uid = 'x' . uniqid();
        $dom_id = $uid;
        CJSCore::Init(array("jquery")); 
        ob_start();
        ?> 
        <script type='text/javascript' src='<?= $pathToLib ?>jpicker-1.1.6.js'></script>
        <input type='text' id='element<?= $dom_id ?>' name='<?= $name ?>' value='<?= $value ?>' />
        <script>
            $(document).ready(function() {
                $('#element<?= $dom_id ?>').jPicker({
                    images:
                            {
                                clientPath: '<?= $pathToLib ?>images/'
                            },
                    localization:
                            {
                                text:
                                        {
                                            title: 'ѕерет¤ните маркер дл¤ выбора цвета',
                                            newColor: 'новый',
                                            currentColor: 'текущий',
                                            ok: 'прин¤ть',
                                            cancel: 'отменить'
                                        },
                                tooltips:
                                        {
                                            colors:
                                                    {
                                                        newColor: 'Ќовый цвет - Ќажмите Уприн¤тьФ дл¤ подтверждени¤',
                                                        currentColor: 'Ќажмите дл¤ возврата первоначального цвета'
                                                    },
                                            buttons:
                                                    {
                                                        ok: 'Ќажмите дл¤ выбора текущего цвета',
                                                        cancel: 'Ќажмите дл¤ возврата первоначального цвета'
                                                    },
                                            hue:
                                                    {
                                                        radio: 'Set To УHueФ Color Mode',
                                                        textbox: 'Enter A УHueФ Value (0-360∞)'
                                                    },
                                            saturation:
                                                    {
                                                        radio: 'Set To УSaturationФ Color Mode',
                                                        textbox: 'Enter A УSaturationФ Value (0-100%)'
                                                    },
                                            value:
                                                    {
                                                        radio: 'Set To УValueФ Color Mode',
                                                        textbox: 'Enter A УValueФ Value (0-100%)'
                                                    },
                                            red:
                                                    {
                                                        radio: 'Set To УRedФ Color Mode',
                                                        textbox: 'Enter A УRedФ Value (0-255)'
                                                    },
                                            green:
                                                    {
                                                        radio: 'Set To УGreenФ Color Mode',
                                                        textbox: 'Enter A УGreenФ Value (0-255)'
                                                    },
                                            blue:
                                                    {
                                                        radio: 'Set To УBlueФ Color Mode',
                                                        textbox: 'Enter A УBlueФ Value (0-255)'
                                                    },
                                            alpha:
                                                    {
                                                        radio: 'Set To УAlphaФ Color Mode',
                                                        textbox: 'Enter A УAlphaФ Value (0-100)'
                                                    },
                                            hex:
                                                    {
                                                        textbox: 'Enter A УHexФ Color Value (#000000-#ffffff)',
                                                        alpha: 'Enter A УAlphaФ Value (#00-#ff)'
                                                    }
                                        }
                            }
                });
            });
        </script>
    <?
        $HTML = ob_get_clean();
        return $HTML;
    }

    function GetEditFormHTML($arUserField, $arHtmlControl) {
        return self::getEditHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], false);
    }

    function GetAdminListEditHTML($arUserField, $arHtmlControl) {
        return self::getViewHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], true);
    }

    function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName) {
        return self::getViewHTML($strHTMLControlName['VALUE'], $value['VALUE']);
    }

    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        return $strHTMLControlName['MODE'] == 'FORM_FILL' ? self::getEditHTML($strHTMLControlName['VALUE'], $value['VALUE'], false) : self::getViewHTML($strHTMLControlName['VALUE'], $value['VALUE']);
    }

};
