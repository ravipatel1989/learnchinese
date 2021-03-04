<?php
defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;

$pinyinData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN ( SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 29 AND c1.content > 1) AND (fieldid = 14 OR fieldid = 28) AND r.dataid = 3 ");
$pinyinData = array_chunk($pinyinData, 2);
$hskData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 47 ) AND (fieldid = 47 OR fieldid = 49) AND r.dataid = 5");
$hskData = array_chunk($hskData, 2);
$i = 0;
$finalArr = array();
foreach($pinyinData as $pinyinKey => $pinyinVal){
    $pinyinChar = $pinyinVal[0]->content;
    $pinyinMultiple = explode(' ', $pinyinVal[1]->content);
    $unsetpinyin = array();
    foreach($hskData as $hskKey => $hskVal){
        $hskChar = $hskVal[0]->content;
        $hskMultiple = $hskVal[1]->content;
        if(strpos($hskChar, $pinyinChar)!==false){ // pinyin character exist in HSK
            foreach($pinyinMultiple as $key => $multiple){
                if(strpos($hskMultiple, $multiple) !== false){ //hskmultiple = wan4 fen1, multiple = wan4
                    $unsetpinyin[] = $multiple;
                }
            }
        }
    }
    $unsetpinyin = array_unique($unsetpinyin);

    $diffArr = array_diff($pinyinMultiple,$unsetpinyin);
    if(!empty($diffArr)){
        $finalArr[$pinyinChar] = $diffArr;
    }
}

if(!empty($finalArr)){ 
    echo '<pre>';
//    print_r($finalArr);
    $sentences = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} AS c WHERE c.fieldid = 67");
    $pinyin = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} As c WHERE c.fieldid = 68");
    $data = array();
    foreach($sentences as $key => $value){
        $data[] = array('sentence' => $value->content, 'pinyin' => $pinyin[$key]->content);
    }
    unset($sentences);
    unset($pinyin);
//    print_r($data);
    $missingpinyin = [];
    foreach ($data as $key => $value){
        foreach($finalArr as $ch => $pinyin){
            foreach($pinyin as $p){
                $value['sentence'] = trim($value['sentence']);
                $value['sentence'] = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $value['sentence']);
                
                $sentencepos = mb_strpos($value['sentence'], $ch);
                $sentenceAddPipe = str_replace(array('1','2','3','4','5'), array('1|','2|','3|','4|','5|'), $value['pinyin']);
                $sentenceAddPipe = rtrim($sentenceAddPipe,'|');
                $sentenceArr = explode('|', $sentenceAddPipe);
                $pinyinpos = array_search($p,$sentenceArr);
                if($sentencepos !== false && $pinyinpos !== false && $sentencepos == $pinyinpos){
                    $charright = $newpinyin = '';
                    for($i=$pinyinpos; $i>=0; $i--){
                        $newpinyin = $sentenceArr[$i].$newpinyin;
                        if(strpos($sentenceArr[$i], " ") !== false || $i==0){
                            $charleft = $i;
                            break;
                        }
                    }
                    
                    for($i = ($pinyinpos+1); $i<=count($sentenceArr); $i++){
                        if(strpos($sentenceArr[$i]," ") !== false){
                            $charright = $i;
                            break;
                        }
                        $newpinyin = $newpinyin.$sentenceArr[$i];
                    }
                    if(!isset($charright) || intval($charright) == 0){
                        $charright = count($sentenceArr);
                    }
                    $lastchar = $charright - $charleft;
                    if($lastchar<0){
                        $lastchar = $lastchar * (-1);
                    }
                    $newchar = mb_substr($value['sentence'], $charleft, $lastchar);
                    $newpinyin = trim($newpinyin);
//                    $missingpinyin[] = array('sentence' => $value['sentence'],'pinyin'=>$value['pinyin'],'ch' => $ch, 'pinyinChar' => $p, 'newsentence' => $newchar, 'newpinyin' => $newpinyin);
                    $missingpinyin[] = array('character' => $newchar, 'pinyin' => $newpinyin);
                    $missingpinyin = array_map("unserialize", array_unique(array_map("serialize", $missingpinyin)));

                    unset($newchar);
                }
            }
        }
    }
    unset($finalArr);
//    print_r($missingpinyin); 
    $file = fopen("learnchinese/csv/characterpinyin.csv","w");

    foreach ($missingpinyin as $line) {
      fputcsv($file, $line);
    }

    fclose($file);
    echo '<div style="display:block; text-align:center;font-size: 20px;text-decoration: underline;font-weight: bold;">';
    echo '<a target="_blank" href="'.$CFG->wwwroot.'/mod/book/learnchinese/csv/characterpinyin.csv">Open CSV</a>';
    echo '</div>';
}

?>