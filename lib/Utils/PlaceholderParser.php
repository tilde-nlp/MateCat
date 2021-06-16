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
        $parsedText = str_replace('##$_A0$##', CatUtils::unicode2chr(0Xa0), $parsedText);

        return $parsedText;
    }

    public static function toXliffFromSymbols($text) {
        $parsedText = str_replace("\n", '<pclf/>', $text);
        $parsedText = str_replace("\r", '<pccr/>', $parsedText);
        $parsedText = str_replace("\r\n", '<pccrlf/>', $parsedText);
        $parsedText = str_replace("\t", '<pctab/>', $parsedText);
        $parsedText = str_replace(CatUtils::unicode2chr(0Xa0), '<pcnb/>', $parsedText);

        return $parsedText;
    }

    public static function toSpaces($text) {
        $parsedText = str_replace('##$_0A$##', " ", $text);
        $parsedText = str_replace('##$_0D$##', " ", $parsedText);
        $parsedText = str_replace('##$_0D0A$##', " ", $parsedText);
        $parsedText = str_replace('##$_09$##', " ", $parsedText);
        $parsedText = str_replace('##$_A0$##', " ", $parsedText);

        $parsedText = preg_replace('/&#x0A;/', " ", $parsedText);
        $parsedText = preg_replace('/&#x0D;/', " ", $parsedText);
        $parsedText = preg_replace('/&#x0D;&#x0A;/', " ", $parsedText);
        $parsedText = preg_replace('/&#x09;/', " ", $parsedText);
        $parsedText = preg_replace('/&#xA0;/', " ", $parsedText);
        $parsedText = preg_replace('/&#nbsp;/', " ", $parsedText);

        $parsedText = str_replace("\n", " ", $text);
        $parsedText = str_replace("\r", " ", $parsedText);
        $parsedText = str_replace("\r\n", " ", $parsedText);
        $parsedText = str_replace("\t", " ", $parsedText);
        $parsedText = str_replace(CatUtils::unicode2chr(0Xa0), " ", $parsedText);

        return $parsedText;
    }

    public static function toPlaceholders($text) {
        $parsedText = str_replace('&lt;pclf/&gt;', '##$_0A$##', $text);
        $parsedText = str_replace('&lt;pccr/&gt;', '##$_0D$##', $parsedText);
        $parsedText = str_replace('&lt;pccrlf/&gt;', '##$_0D0A$##', $parsedText);
        $parsedText = str_replace('&lt;pctab/&gt;', '##$_09$##', $parsedText);
        $parsedText = str_replace('&lt;pcnb/&gt;', '##$_A0$##', $parsedText);

        $parsedText = str_replace('<pclf/>', '##$_0A$##', $text);
        $parsedText = str_replace('<pccr/>', '##$_0D$##', $parsedText);
        $parsedText = str_replace('<pccrlf/>', '##$_0D0A$##', $parsedText);
        $parsedText = str_replace('<pctab/>', '##$_09$##', $parsedText);
        $parsedText = str_replace('<pcnb/>', '##$_A0$##', $parsedText);

        return $parsedText;
    }

    private static function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }
}

