<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$msg = '';
/*----------------------- Multiple pinyin character --------------------------*/
$multipleCharacter = $DB->get_records_sql("SELECT r.id, content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (SELECT r.id FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '29' AND c.content > 0 AND r.dataid = 3) AND c.fieldid LIKE '28'");
foreach($multipleCharacter as $key => $value){
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
$multipleCharCounts = array_count_values($pinyinValues);
unset($multipleCharacter);
/*----------------------- Single pinyin character ----------------------------*/
$characterData = $DB->get_records_sql("SELECT r.id, content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '28' AND r.dataid = 3");
$pinyinValues = [];
foreach($characterData as $key => $value){
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
unset($characterData);

$pinyinData = $DB->get_records_sql('SELECT r.id, c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 1');

$pinyinArr = $uniqueArr = array();
foreach($pinyinData as $id => $record) {
    $pinyinArr[$record->recordid][$record->fieldid] = $record->content;
}
usort($pinyinArr, function($a, $b) {
    return $a[21] <=> $b[21];
});
$uniqueGroupCnt = [];
$uniqueCnt = 1;
foreach($pinyinArr as $key => $value){
    if($value[3] == 'U'){
        $uniqueArr[] = $value;
        $uniqueGroupCnt[$value[8]] = ++$uniqueCnt;
    }
}
?>
<span id="Ugroups" class="hidden" data-unique="<?php echo implode(',', $uniqueGroupCnt); ?>"></span>
<table id="uniqueworksheet" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="1" id="pinyinleft"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></th>
            <?php foreach($uniqueArr as $key => $value){
                echo '<th scope="col" width="50">'.$value[1].'</th>';
            }
            ?>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="0" id="pinyinright"><i class="fa fa-arrow-right" aria-hidden="true"></i></a></th>
        </tr>
    </thead>
    <tbody>
        <tr class="nohidden">
            <td class="nohidden"></td>
            <?php foreach($uniqueArr as $key => $value){
                    if(!isset($value[60]) || $value[60]==""){
                        echo '<td><b>'.$value[2].'</b></td>';
                    }else{
                        echo '<td><div class="tooltipcustom"><b>'.$value[2].'</b><span class="tooltiptext">'.$value[60].'</span></div></td>';
                    }
            }
            ?>
            <td class="nohidden"></td>
        </tr>
        <?php
            echo '<tr class="nohidden">';
            $cnt = 0;
            echo '<td class="nohidden">Character</td>';
            foreach($uniqueArr as $key => $value){
                    $cnt++;
                    if(intval($pinyincounts[$value[1]] > 0)){
                        echo '<td><a href="'.$CFG->wwwroot.'/mod/book/view.php?id=15&chapterid=13&search='.$value[1].'">'.$pinyincounts[$value[1]].'</a></td>';
                    }else{
                        echo '<td>X</td>';
                    }
            }
            echo '<td class="nohidden"></td>';
            echo '</tr>';
        ?>
        <?php
            echo '<tr class="nohidden">';
            $cnt = 0;
            echo '<td class="nohidden">Multiple Pinyin</td>';
            foreach($uniqueArr as $key => $value){
                    $cnt++;
                    if(intval($multipleCharCounts[$value[1]] > 0)){
                        echo '<td><a href="'.$CFG->wwwroot.'/mod/book/view.php?id=15&chapterid=14&search='.$value[1].'">'.$multipleCharCounts[$value[1]].'</a></td>';
                    }else{
                        echo '<td>X</td>';
                    }
            }
            echo '<td class="nohidden"></td>';
            echo '</tr>';
        ?>
    </tbody>
</table>
