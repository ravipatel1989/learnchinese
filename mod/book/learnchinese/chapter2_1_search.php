<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$searchVal = filter_input(INPUT_GET, 'search');

$sql = "SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select RIGHT(content, 1) FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS tonenumber FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE (c.content LIKE '".$searchVal."%') AND c.fieldid = 23 AND r.dataid = 3";
$pinyinData = $DB->get_records_sql($sql);
$pinyinArr = [];
$i = 0;
foreach($pinyinData as $key => $value){
    if (strpos($value->content, $searchVal) !== false) {
        $content = explode(' ', $value->content);
        if(!empty($content) && count($content) > 1){
            foreach($content as $k => $v){
                if (strpos($v, $searchVal) !== false) {
                    $tonenumber = substr($v, -1);
                    $pinyinArr[$tonenumber][]['charcontent'] = $value->charcontent;
                    $i++;
                }
            }            
        }else{
            $pinyinArr[$value->tonenumber][]['charcontent'] = $value->charcontent;
            $i++;
        }       
    }
}

ksort($pinyinArr);

$goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 6]); 
?>
<div class="align-right">
<a href="<?php echo $goback; ?>" class="btn btn-info" role="button" style=" margin-bottom:5px;">Go back</a>   
</div>
<div class="clear"></div>
<!--<table id="charactertoneTbl" width="100%" border="1">-->
<table id="radicalSearch" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Tone 1</th>
            <th>Tone 2</th>
            <th>Tone 3</th>
            <th>Tone 4</th>
            <th>Tone 5</th>
        </tr>
    </thead>
    <tbody>
        
        <?php 
        if(!array_key_exists('1',$pinyinArr)) { $pinyinArr['1'][] = array('charcontent'=>'X');} 
        if(!array_key_exists('2',$pinyinArr)) { $pinyinArr['2'][] = array('charcontent'=>'X');}
        if(!array_key_exists('3',$pinyinArr)) { $pinyinArr['3'][] = array('charcontent'=>'X');}
        if(!array_key_exists('4',$pinyinArr)) { $pinyinArr['4'][] = array('charcontent'=>'X');}
        if(!array_key_exists('5',$pinyinArr)) { $pinyinArr['5'][] = array('charcontent'=>'X');}
        
        $maxlen = count(max($pinyinArr));
        for($i=0;$i<$maxlen;$i++){
            echo '<tr>';
            for($j=1;$j<=5;$j++){
                if(isset($pinyinArr[$j][$i]['charcontent'])){
                    $mp4file = $CFG->wwwroot.'/characterdata/'.$pinyinArr[$j][$i]['charcontent'].'.mp4';
                    if(!file_exists($CFG->characterdata.'/'.$v['charcontent'].'.mp4')){
                        $mp4file = $CFG->wwwroot.'/characterdata/'.$pinyinArr[$j][$i]['charcontent'].'-多音字.mp4';
                    }
                    echo '<td><a href="'.$mp4file.'" target="_blank">'.$pinyinArr[$j][$i]['charcontent'].'</a></td>';
                }else{
                    echo '<td></td>';
                }    
            }
            echo '</tr>';
        }
        ?>
    </tbody>
</table>