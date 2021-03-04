<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
if(isset($_POST['submit']) && $_POST['submit'] == 'Update'){
    $pinyinData = $DB->get_records_sql("SELECT c.recordid, c.content, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 65) AS word_cnt, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 67 AND r.dataid = 9 order by c.recordid");

    foreach($pinyinData as $key => $value){
        $wordcnt = $value->word_cnt;
        $pinyin = $value->pinyin;
        $pinyinSpaceCnt = substr_count($pinyin, ' ');
        $pinyinSpaceCnt = $pinyinSpaceCnt + 1;
        if($wordcnt != $pinyinSpaceCnt){
            $sql = "UPDATE {data_content} SET content = '".$pinyinSpaceCnt."' WHERE recordid = $key AND fieldid = 65";
            $DB->execute($sql);
        }
    }
}
?>
<form action="" method="post">
    <input type="submit" class="btn btn-primary" value="Update" name="submit" />
</form>