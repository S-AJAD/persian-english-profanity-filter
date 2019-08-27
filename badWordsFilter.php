<?php

/*

Profanity Filter
by Sadjad Momeni-Moghaddam
Github: https://github.com/S-AJAD
LinkedIn: https://www.linkedin.com/in/sadjadmoghaddam/
---------
tested on PHP verison 7.3.7
27/08/2019

*/

require_once('Persian.php');

class badWordsFilter {

    // $count: number of detected bad words 
    private static $count=0;

    // normalize characters using Persian class (Persian.php)
    private static function normalize($sentence) {

        $result = Persian::standard($sentence);
        return $result;

    }

    // read bad words' list from list.txt 
    private static function getBadWordsArray(){

        $fp = @fopen('list.txt', 'r'); 
        if ($fp) {
            $array = explode("\n", fread($fp, filesize('list.txt')));
            $array = array_unique($array);
        }
        return $array;
        
    }

    // main function to filter bad words from a given sentence
    // usingSimilarity can be used to filter words which are similar to bad words
    // riskLevel can be used to indicate number of acceptable bad words. if the number of badwords is higher, response is false
    public static function filterBadWords($sentence, $usingSimilarity=false, $usingRiskLevel= false, $riskLevel= 5, $replace = ' **** '){
        
        $normalizedSentence = self::normalize($sentence);
        if ($usingSimilarity) {
            $r =  self::removeSimilarBadWords(self::filter(self::filterDuplicatedBadWords($normalizedSentence, $replace), $replace),$replace);
            if ($usingRiskLevel && self::$count >= $riskLevel) return false;
            return $r;
        }else {
            $r = self::filter(self::filterDuplicatedBadWords($normalizedSentence, $replace),$replace);
            if ($usingRiskLevel && self::$count >= $riskLevel) return false;
            return $r;
        }

    }

    // find words in sentence which are similar to bad words list
    private static function removeSimilarBadWords($sentence, $replace, $treshhold = 90) {

        $resStr = $sentence;
        $data   = preg_split('/\s+/', $sentence);
        $array = self::getBadWordsArray();
        foreach ($array as $badword) {
            if($badword != "") {
                foreach ($data as $word) {
                    similar_text($word, $badword, $per);
                    if ($per > $treshhold) {
                        $resStr = str_replace($word, $replace, $resStr);
                        self::$count ++;
                    }
                }
            }
        }
        return $resStr;

    }

    // remove every bad words in sentence
    private static function filter($sentence, $replace) {

        $array = self::getBadWordsArray();
        $clean = str_ireplace($array, $replace, $sentence, $counter);
        self::$count = self::$count + $counter;
        return ($clean);

    }

    // remove badwords which have some duplicated characters
    private static function filterDuplicatedBadWords($sentence, $replace){

        $words  = preg_split('/\s+/', $sentence);
        $array = self::getBadWordsArray();
        $res = $sentence;
        foreach($words as $word) {
            $correctForm = self::removeDuplicateCharacters($word);
            if (in_array($correctForm, $array,true) ) {
                self::$count ++;
                
                $res = str_ireplace($word,  $replace, $res);
            }
        }
        return $res;

    }

    // remove all duplicated character from a sentence or word which are side by side
    private static function removeDuplicateCharacters($sentence) {

        $i =0;
        while ($i<(mb_strlen($sentence, 'utf-8'))-1) {
            $char1 = mb_substr($sentence, $i, 1, 'utf-8');
            $char2 = mb_substr($sentence, $i+1, 1, 'utf-8');
            if(strcmp($char1, $char2)==0) {
                $str1 = mb_substr($sentence,0,$i);
                $str2 = mb_substr($sentence,$i+1,strlen($sentence));
                $sentence = $str1.$str2;
            }else {
                $i = $i+1;
            }
        }
        return $sentence;

    }
    
}

?>