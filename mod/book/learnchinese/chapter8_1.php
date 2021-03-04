<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
if(isset($_POST['submit']) && $_POST['submit'] == 'Update'){
    $contentData = $DB->get_records_sql("SELECT c.recordid, c.content, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 66) AS character_cnt, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 67 AND r.dataid = 9 order by c.recordid");

    foreach($contentData as $key => $value){

        $onlycharacters = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $value->content);
        $characterlength = mb_strlen($onlycharacters);
        $pinyin = $pinyinData[$key]->content;
        $pinyincounts = preg_match_all( "/[0-9]/", $pinyin );
        if($characterlength != $value->character_cnt){
            $sql = "UPDATE {data_content} SET content = '".$characterlength."' WHERE recordid = $key AND fieldid = 66";
            $DB->execute($sql);
        }
    }
}
?>
<form action="" method="post">
    <input type="submit" class="btn btn-primary" value="Update" name="submit" />
</form>