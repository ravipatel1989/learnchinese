<?php
defined('MOODLE_INTERNAL') || die();
global $DB;

$pinyinData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN ( SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 29 AND c1.content > 1) AND (fieldid = 14 OR fieldid = 28) AND r.dataid = 3 ");
$pinyinData = array_chunk($pinyinData, 2);
//$hskData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 47  AND CHAR_LENGTH(c1.content) > 1) AND (fieldid = 47 OR fieldid = 49) AND r.dataid = 5");
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
    ?>
<table id="missinghsk" class="table table-bordered table-striped">
    <thead>
        <tr><th>Character</th><th>Pinyin</th></tr>
    </thead>
    <tbody>
        <?php foreach($finalArr as $key => $pinyinArr){ ?>
            <?php foreach($pinyinArr as $value){ ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $value; ?></td>
            </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
<?php }?>