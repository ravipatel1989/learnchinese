<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;

$postchar = "";
if(isset($_POST) && $_POST['updatepiyin']!=""){
    $updatepinyin = explode(' ', $_POST['updatepiyin']);
    $postchar = $updatepinyin[0];
    $postpinyin = $updatepinyin[1];
}
$sesskey = sesskey();
$sentencesData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE (c.content NOT LIKE '%1' AND c.content NOT LIKE '%2' AND c.content NOT LIKE '%3' AND c.content NOT LIKE '%4' AND c.content NOT LIKE '%5') AND fieldid = 68 AND r.dataid = 9 order by c.id");
foreach($sentencesData as $key => $sentence){
    $pinyin = $sentence->content.'5';
    $sql = "UPDATE {data_content} SET content = '$pinyin' WHERE id = {$key}";
    $update = $DB->execute($sql);
}

$contentData = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 67 AND r.dataid = 9 order by c.recordid");

$pinyinData = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 68 AND r.dataid = 9 order by c.recordid");

//print_r(current($contentData));
$unmatchData = array();
if(isset($_POST['action']) && $_POST['action'] != ''){
    $updatepinyin = explode(' ', $_POST['action']);
    $postchar = $updatepinyin[0];
    $postpinyin = $updatepinyin[1];
    foreach ($contentData as $key => $value){
        $character = preg_replace('/[0-9]+/', '', $value->content);
        $tempchar = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $character);
        $characterlength = mb_strlen($tempchar);
        $pinyin = $pinyinData[$key]->content;
        $pinyinArr = str_replace(array('1','2','3','4','5'), array('1|','2|','3|','4|','5|'), $pinyin);
        $pinyinArr = explode('|', $pinyinArr);
        $pinyinArr = array_filter($pinyinArr);
        $pinyinlen = count($pinyinArr);
        if($characterlength != $pinyinlen){
            if(strpos($character,$postchar) !== false){
                $charpos = mb_strpos($tempchar, $postchar);
                $lastchar = substr($pinyinArr[$charpos], -1);
                if(strpos($pinyinArr[$charpos],$postpinyin.'1') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'2') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'3') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'4') === false){
                $pinyinArr[$charpos] = str_replace($postpinyin, $postpinyin.'5', $pinyinArr[$charpos]);
                }
                $pinyin = implode('', $pinyinArr);

                $sql = "UPDATE {data_content} SET content = '".$pinyin."' WHERE recordid = $key AND fieldid = 68";
                $update = $DB->execute($sql);
//                echo $sql;
//                echo '<br>';
            }
        }
    }
    unset($_POST);
    $postchar = $postpinyin = "";
}
?>
<form name="missingpinyinFrm" action="" method="POST">
    <?php if(isset($_POST['updatepiyin']) && $_POST['updatepiyin']!=""){?>
    <input type="hidden" name="action" value="<?php echo $_POST['updatepiyin']; ?>" /> 
    <?php }?>
    <input type="hidden" name="updatepiyin" id="updatepiyin" value="<?php echo $_POST['updatepiyin']; ?>" />
    <?php if(isset($_POST['updatepiyin']) && $_POST['updatepiyin']!=""){
        $value = "Submit";
        $tablename = "mismatchpinyinsubmit";
    }else{
        $value = "Update";
        $tablename = "mismatchpinyin";
    }
    ?>
    <input type="Submit" id="update" value="<?php echo $value; ?>" style="float: right;" />
    <?php if(isset($_POST['updatepiyin']) && $_POST['updatepiyin']!=""){
        echo '<span style="float:right; font-weight:bold; line-height:2.5;">Character: '.$postchar.', Pinyin: '.$postpinyin.'</span>';
    }?>
    
</form>    
<?php
echo '<table id="'.$tablename.'" class="table table-bordered table-striped">';
echo '<thead>';
echo '<tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>';
echo '</thead>';
echo '<tbody>';
foreach ($contentData as $key => $value){
    $character = preg_replace('/[0-9]+/', '', $value->content);
    $tempchar = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $character);
    $characterlength = mb_strlen($tempchar);
    $pinyin = $pinyinData[$key]->content;
    $pinyinArr = str_replace(array('1','2','3','4','5'), array('1|','2|','3|','4|','5|'), $pinyin);
    $pinyinArr = explode('|', $pinyinArr);
    $pinyinArr = array_filter($pinyinArr);
    $pinyinlen = count($pinyinArr);
    if($characterlength != $pinyinlen){
        if(strpos($character,$postchar) !== false){
            $charpos = mb_strpos($tempchar, $postchar); 
//            echo $pinyinArr[$charpos];
            $lastchar = substr($pinyinArr[$charpos], -1);
            if(strpos($pinyinArr[$charpos],$postpinyin.'1') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'2') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'3') === false &&
                   strpos($pinyinArr[$charpos],$postpinyin.'4') === false){
                $pinyinArr[$charpos] = str_replace($postpinyin, $postpinyin.'5', $pinyinArr[$charpos]);
            }
            $pinyin = implode('', $pinyinArr);
            echo '<tr><td>'.$character.'</td><td>'.$pinyin.'</td><td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$key.'&sesskey='.$sesskey.'">Edit</a></td></tr>';
        }
        
    }
    if($characterlength != $pinyinlen && $postchar==""){
            echo '<tr><td>'.$character.'</td><td>'.$pinyin.'</td><td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$key.'&sesskey='.$sesskey.'">Edit</a></td></tr>';
    }
}
echo '</tbody>';
echo '</table>';
?>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#mismatchpinyin').on('search.dt', function() {
            var value = $('.dataTables_filter input').val();
            $("#updatepiyin").val(value);
        });
    });
</script>