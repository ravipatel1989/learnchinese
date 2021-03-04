<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;

// get keywords from grammer table
$sql = "SELECT c1.recordid, REPLACE(c1.content, ' ', '') as content,(SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 96 AND c2.recordid = c1.recordid) as id FROM {data_content} as c1 WHERE c1.fieldid = 99 AND c1.recordid = 152967";
$keywords = $DB->get_records_sql($sql);

$sql = "SELECT c.recordid, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) AS sentence, (SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 106 AND c2.recordid = c.recordid) AS GID FROM {data_content} AS c WHERE c.fieldid = 105 AND c.content = 1 AND c.recordid = 154316 limit 10";
$sentenceList = $DB->get_records_sql($sql);

$counter = 0;
foreach($sentenceList as $sentence){
    $GID = [];
    foreach($keywords as $keyword){
        $regex = "/[.，*？!！＇’\"@#$&-_ ]+$/";
        $keyword->content = preg_replace($regex, "", $keyword->content);
        $boolchar = preg_match("/\p{Han}+/u", $keyword->content);
        if($boolchar == 1){
            $counter++;
            $keyword->content = preg_replace('/\[.*\]/', '', $keyword->content);
            $keyword->content = preg_replace('/\[.*/', '', $keyword->content);
            $keyword->content = str_replace('(', '', $keyword->content);
            $keyword->content = str_replace(')', '', $keyword->content);
            $keyword->content = str_replace('、', ',', $keyword->content);
            $keyword->content = str_replace('，', ',', $keyword->content);
            $keyword->content = str_replace('++', '+', $keyword->content);
            $keyword->content = trim($keyword->content,'+');
            $pluskeyword = array($keyword->content);
            $GID = $sentence->GID;
//            echo '========'.$keyword->content.'=========';
//            echo '<br>';
//            echo '========'.$sentence->sentence.'=========';
//            echo '<br>';
            
            if(strpos($keyword->content, '+') !== false){
                $pluskeyword = explode('+', $keyword->content);
            }
            $finalArr = array();
            $commacount = 0;
            foreach($pluskeyword as $key => $commakeywords){
                $commakeywordsArr = preg_split('/(,|\/)/', $commakeywords,-1, PREG_SPLIT_NO_EMPTY);
                $pluskeyword[$key] = $commakeywordsArr;
            }
            $countpluskeyword = count($pluskeyword);
            $finalkeywordArr = [];
            if($countpluskeyword==1){
                $finalkeywordArr = $pluskeyword[0];
            }
            else{
                for($i=0; $i<$countpluskeyword; $i++){
                    $countj = count($pluskeyword[$i]);
                    for($j=0;$j<$countj;$j++){
                        $countk = count($pluskeyword[$i+1]);
                        for($k=0;$k<$countk;$k++){
                            $finalkeywordArr[$i][] = $pluskeyword[$i][$j].'-'.$pluskeyword[$i+1][$k];
                        }
                    }
                    $pluskeyword[$i+1] = $finalkeywordArr[$i];
                }
                $finalkeywordArr = end($finalkeywordArr);
            }
            
            foreach($finalkeywordArr as $fkeyword){
                $fkeyword = explode('-', $fkeyword);
                $totalchar = count($fkeyword);
                $sentenceval = $sentence->sentence;
                $cnt = 0;
                for($i=0; $i<$totalchar; $i++){
                    if(mb_strpos($sentenceval, $fkeyword[$i]) !== false){
                        $cnt++;
                        $sentenceval = mb_substr($sentenceval, mb_strpos($sentenceval, $fkeyword[$i]) + 1);
                    }
                }
                if($cnt==$totalchar){
                    $GID[] = $keyword->id;
                    break;
                }
            }
        }
    }
    if(!empty($GID)){
        $selectQry = "SELECT content FROM {data_content} WHERE fieldid = 106 AND recordid = '$sentence->recordid'";
        $existingGid = $DB->get_record_sql($selectQry);
        $existingGid = $existingGid->content;
        $GID = implode(',', $GID);
        if(intval($existingGid) > 0){
            $GID = $GID.','.$existingGid;
        }
        $GID = explode(',', $GID);
        $GID = array_unique($GID);
        $GID = implode(',', $GID);
        echo '<br>';
        echo $sql = "UPDATE `{data_content}` SET `content` = '$GID' WHERE `fieldid` = 106 AND `recordid` = '$sentence->recordid'"; 
        echo '<br>';
//        $updateClass = $DB->execute($sql);
    }
}
?>
<table id="senteceGID_bkp" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Sentence</th><th>GID</th>
        </tr>
    </thead>
</table>