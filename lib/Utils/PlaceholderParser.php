<?php

class PlaceholderParser {

    public static function toXliff($text) {
        $parsedText = str_replace('##$_0A$##', '<pclf/>', $text);
        $parsedText = str_replace('##$_0D$##', '<pccr/>', $parsedText);
        $parsedText = str_replace('##$_0D0A$##', '<pccrlf/>', $parsedText);
        $parsedText = str_replace('##$_09$##', '<pctab/>', $parsedText);
        $parsedText = str_replace('##$_A0$##', '<pcnb/>', $parsedText);

        return $parsedText;
    }

    public static function toPlaceholders($text) {
        $parsedText = str_replace('<pclf/>', '##$_0A$##', $text);
        $parsedText = str_replace('<pccr/>', '##$_0D$##', $parsedText);
        $parsedText = str_replace('<pccrlf/>', '##$_0D0A$##', $parsedText);
        $parsedText = str_replace('<pctab/>', '##$_09$##', $parsedText);
        $parsedText = str_replace('<pcnb/>', '##$_A0$##', $parsedText);

        return $parsedText;
    }
}

