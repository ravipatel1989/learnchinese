<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$msg = '';
if(isset($_GET['refresh']) && $_GET['refresh']=='pinyin'){
    
    /*----------------------------------------------------------------------- */
    /*------- Set multiple pinyin to 9 if file change to ch-多音字.mp4 ------- */
    /*----------------------------------------------------------------------- */
    $nullMultiplePinyin = $DB->get_records_sql("SELECT recordid, content FROM {data_content} WHERE fieldid = 14 AND recordid IN (SELECT r.id FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '29' AND c.content = '' AND r.dataid = 3)");
    $updateMParray = [];

    foreach($nullMultiplePinyin as $key => $value){
        if(file_exists($CFG->characterdata.'/'.$value->content.'-多音字.mp4')){
            $updateMParray[] = $key;
        }
    }
    $totalRec = count($updateMParray);
    if($totalRec > 0){
        $recordids = implode(',', $updateMParray);
        $sql = "UPDATE {data_content} SET content = 9 WHERE recordid IN ($recordids) AND fieldid = 29";
        $update = $DB->execute($sql);
        if($update == 1){
            if($totalRec == 1){
                    $msg = "1 record set to multiple pinyin 9.";
            }else{
                    $msg = "$totalRec records set to multiple pinyin 9.";
            }
        }
    }
    /*----------------------------------------------------------------------- */
    /*----------- Reset multiple pinyin to null if file not found ----------- */
    /*----------------------------------------------------------------------- */
    $multiplePinyin = $DB->get_records_sql("SELECT recordid, content FROM {data_content} WHERE fieldid = 14 AND recordid IN (SELECT r.id FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '29' AND c.content > 0 AND r.dataid = 3)");
    $updateMParray = [];
    
    foreach($multiplePinyin as $key => $value){
        $file1 = (bool)file_exists($CFG->characterdata.'/'.$value->content.'.mp4');
        $file2 = (bool)file_exists($CFG->characterdata.'/'.$value->content.'-多音字.mp4');
        if($file1 === false && $file2 === false){
            $updateMParray[] = $key;
        }
    }
    $totalRec = count($updateMParray);
    if($totalRec > 0){
        $recordids = implode(',', $updateMParray);
        $sql = "UPDATE {data_content} SET content = '' WHERE recordid IN ($recordids) AND fieldid = 29";
        $update = $DB->execute($sql);
        if($update == 1){
            if($msg!=""){
                $msg .= "<br>";
            }
            if($totalRec == 1){
                    $msg .= "1 record set to multiple pinyin null.";
            }else{
                    $msg .= "$totalRec records set to multiple pinyin null.";
            }
        }
    }
    if($msg != ""){
        echo '<br><div data-rel="success" class="box alert alert-success msgdiv"><p style="margin-bottom:0;">'.$msg.'</p></div>';
    }else{
        echo '<br><div data-rel="success" class="box alert alert-success msgdiv"><p style="margin-bottom:0;">No record updated</p></div>';
    }
}

// number of characters with multiple_pinyin > zero

$pinyinData = $DB->get_records_sql("SELECT r.id, content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (SELECT r.id FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '29' AND c.content > 0 AND r.dataid = 3) AND c.fieldid LIKE '28'");

foreach($pinyinData as $key => $value){
    $pinyin = preg_replace('/[0-9]+/', '', $value->content);
    $content = explode(' ', $pinyin);
    $content = array_unique($content);
    $totalcontent = count($content);
    if(!empty($totalcontent)){
        foreach($content as $k => $v){
            $pinyinValues[] = $v;
        }
    }else{
        $pinyinValues[] = $pinyin;
    }
}
$pinyincounts = array_count_values($pinyinValues);
unset($pinyinData);
if(!isset($_GET['search'])){
    
/*---------------------------- get master table ------------------------------*/

$pinyinData = $DB->get_records_sql('SELECT r.id, c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 1');
$pinyinArr = $initialArr = $finalArr = $uniqueArr = array();
foreach($pinyinData as $id => $record) {
    $pinyinArr[$record->recordid][$record->fieldid] = $record->content;
}
usort($pinyinArr, function($a, $b) {
    return $a[21] <=> $b[21];
});
$initialGroupCnt = $finalGroupCnt = [];
$initialCnt = $finalCnt = 2;
foreach($pinyinArr as $key => $value){
    if($value[3] == 'I'){
        $initialArr[] = $value;
        $initialGroupCnt[$value[8]] = ++$initialCnt;
    }
    if($value[3] == 'F'){
        $finalArr[] = $value;
        $finalGroupCnt[$value[8]] = ++$finalCnt;
    }
    if($value[3] == 'U'){
        $uniqueArr[] = $value;
    }
}
$lastEle = array_key_last($initialGroupCnt);
$initialGroupCnt[$lastEle] = $initialGroupCnt[$lastEle] + 1; 
$uniqueArrColumns = array_column($uniqueArr, 1);
//$uniqueArrColumns = array_column($uniqueArr, 0);
if(is_siteadmin()){
    $refresh = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 14, 'refresh' => 'pinyin']); 
    echo '<div class="align-right">';
    echo '<a href="'.$refresh.'" class="btn btn-success" role="button" style="margin-bottom:5px;">Refresh</a>';
    echo '</div>';
    echo '<div class="clear"></div>';
}
?>
<table id="pinyinworksheet" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th scope="col" class="nohidden" width="50"><span id="IFgroups" class="hidden" data-initial="<?php echo implode(',', $initialGroupCnt); ?>" data-final="<?php echo implode(',', $finalGroupCnt); ?>"></span></th>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="0" id="pinyinleft"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></th>
            <?php foreach($initialArr as $key => $value){
                echo '<th scope="col" width="50">'.$value[1].'</th>';
            }
            ?>
            <th scope="col" width="50">∅</th>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="1" id="pinyinright"><i class="fa fa-arrow-right" aria-hidden="true"></i></a></th>
        </tr>
    </thead>
    <tbody>
        <tr class="nohidden">
            <td class="nohidden"><a href="javascript:void(0);" data-num="0" id="pinyinup"><i class="fa fa-arrow-up" aria-hidden="true"></i></a></td>
            <td class="nohidden"></td>
            <?php foreach($initialArr as $key => $value){
                    if(!isset($value[60]) || $value[60]==""){
                        echo '<td><b>'.$value[2].'</b></td>';
                    }else{
                        echo '<td><div class="tooltipcustom"><b>'.$value[2].'</b><span class="tooltiptext">'.$value[60].'</span></div></td>';
                    }
            }
            ?>
            <td></td>
            <td class="nohidden"></td>
        </tr>
        <?php 
        foreach($finalArr as $key => $value){
            echo '<tr>';
                echo '<td class="nohidden"><b>'.$value[1].'</b></td>';
                if(!isset($value[60]) || $value[60]==""){
                    echo '<td class="nohidden"><b>'.$value[2].'</b></td>';
                }else{
                    echo '<td class="nohidden"><div class="tooltipcustom"><b>'.$value[2].'</b><span class="tooltiptext">'.$value[60].'</span></div></td>';
                }
                for($i=0;$i<24;$i++){
                    $finalVal = $finalArr[$key][1];
                    if($finalArr[$key][1] == "u" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ü";
                    }
                    if($finalArr[$key][1] == "ü" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "u";
                    }
                    if($finalArr[$key][1] == "üe" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ue";
                    }
                    if($finalArr[$key][1] == "ue" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "üe";
                    }
                    if($finalArr[$key][1] == "ün" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "un";
                    }
                    if($finalArr[$key][1] == "un" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ün";
                    }
                    $arrval = $initialArr[$i][1].$finalVal;
                    if(!empty($pinyincounts[$arrval]) && intval($pinyincounts[$arrval] > 0)){
                        echo '<td><a href="'.$CFG->wwwroot.'/mod/book/view.php?id=15&chapterid=24&search='.$arrval.'">'.$pinyincounts[$arrval].'</a></td>';
                    }else{
                        echo '<td>X</td>';
                    }
                }
                echo '<td class="nohidden"></td>';
            echo '</tr>';
        }
        ?>
        <?php for($i=0;$i<2;$i++){ ?>
        <tr class="nohidden">
            <td class="nohidden"><?php if($i==0){ ?><a href="javascript:void(0);" data-num="1" id="pinyindown"><i class="fa fa-arrow-down" aria-hidden="true"></i></a><?php }else{ ?><br><?php } ?></td>
            <td class="nohidden"></td>
            <?php
            for($i=0;$i<24;$i++){
                echo '<td></td>';
            }
            ?>
            <td class="nohidden"></td>
        </tr>    
        <?php }?>
        
    </tbody>
</table>
<?php }else{
    $searchVal = filter_input(INPUT_GET, 'search');
    
    $sql = "SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select content FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE (c.content LIKE '".$searchVal."1%' OR c.content LIKE '".$searchVal."2%' OR c.content LIKE '".$searchVal."3%' OR c.content LIKE '".$searchVal."4%' OR c.content LIKE '".$searchVal."5%' OR c.content LIKE '% ".$searchVal."1%' OR c.content LIKE '% ".$searchVal."2%' OR c.content LIKE '% ".$searchVal."3%' OR c.content LIKE '% ".$searchVal."4%' OR c.content LIKE '% ".$searchVal."5%') AND c.recordid IN (SELECT r1.id FROM {data_content} AS c1 LEFT JOIN {data_records} AS r1 ON c1.recordid = r1.id WHERE c1.fieldid = '29' AND c1.content > 0 AND r1.dataid = 3) AND c.fieldid = 28 AND r.dataid = 3";    
    $pinyinData = $DB->get_records_sql($sql);
    $goback = new moodle_url('/mod/book/view.php',['id'=>15, 'chapterid'=>24]);
    echo '<div class="goback" style="text-align:right;">';
    echo '<a href="'.$goback.'" class="btn btn-info" role="button" style="margin-bottom:5px;">Go back</a>';
    echo '</div>';
    echo '<div class="clear"></div>';
    
    echo '<table class="table table-bordered table-striped">';
    echo '<thead>';
        echo '<tr align="center">';
        echo '<th width="20%">#</th>';
        echo '<th width="30%">Pinyin</th>';
        echo '<th width="25%">Chinese Character</th>';
        echo '<th width="25%">Action</th>';
        echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    $cnt = 0;
    foreach($pinyinData as $key => $value){
        if(strpos($value->pinyin,$searchVal) !== false){
            $cnt++;
            echo '<tr align="center">';
            echo '<td>'.$cnt.'</td>';
            echo '<td class="pinyinval">'.$value->pinyin.'</td>';
            $implodeVal = str_replace(' ', '_', $value->pinyin);
            $charactersearch = new moodle_url('/mod/book/view.php',['id'=>15, 'chapterid'=>24, 'chinesechar'=>$value->charcontent, 'pinyin'=>$searchVal, 'recordid' => $value->id, 'searchpinyin' => $implodeVal]);
            
            echo '<td>'.$value->charcontent.'</td>';
            echo '<td><a href="'.$charactersearch.'" class="btn btn-primary" role="button">View</a></td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';
    if(is_siteadmin()){
        $url = new moodle_url('/mod/data/edit.php');
    }
}
