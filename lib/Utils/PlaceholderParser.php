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

    public static function toSymbols($text) {
        $parsedText = str_replace('##$_0A$##', "\n", $text);
        $parsedText = str_replace('##$_0D$##', "\r", $parsedText);
        $parsedText = str_replace('##$_0D0A$##', "\r\n", $parsedText);
        $parsedText = str_replace('##$_09$##', "\t", $parsedText);
        $parsedText = str_replace('##$_A0$##', " ", $parsedText);

        return $parsedText;
    }

    public static function toSpaces($text) {
        $parsedText = str_replace('##$_0A$##', " ", $text);
        $parsedText = str_replace('##$_0D$##', " ", $parsedText);
        $parsedText = str_replace('##$_0D0A$##', " ", $parsedText);
        $parsedText = str_replace('##$_09$##', " ", $parsedText);
        $parsedText = str_replace('##$_A0$##', " ", $parsedText);

        return $parsedText;
    }

    public static function toPlaceholders($text) {
        $parsedText = str_replace('&lt;pclf/&gt;', '##$_0A$##', $text);
        $parsedText = str_replace('&lt;pccr/&gt;', '##$_0D$##', $parsedText);
        $parsedText = str_replace('&lt;pccrlf/&gt;', '##$_0D0A$##', $parsedText);
        $parsedText = str_replace('&lt;pctab/&gt;', '##$_09$##', $parsedText);
        $parsedText = str_replace('&lt;pcnb/&gt;', '##$_A0$##', $parsedText);

        return $parsedText;
    }
}

