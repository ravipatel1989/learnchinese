<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;

$sesskey = sesskey();

$contentData = $DB->get_records_sql("SELECT c.recordid, c.content, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 66) AS character_cnt, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 67 AND r.dataid = 9 order by c.recordid");

?>
<h2><ul>Point 1</ul></h2>
<table id="mismatchpinyin" class="table table-bordered table-striped">    
<thead>
<tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>
</thead>
<tbody>
<?php
foreach($contentData as $key => $value){
    
    $onlycharacters = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $value->content);
    $characterlength = mb_strlen($onlycharacters);
//    $characterlength = mb_strlen($value->content);
    $pinyin = $pinyinData[$key]->content;
    $pinyincounts = preg_match_all( "/[0-9]/", $pinyin );
    if($characterlength != $value->character_cnt){
        echo '<tr><td>'.$onlycharacters.' ('.$value->character_cnt.') ('.$characterlength.')</td><td>'.$value->pinyin.'</td><td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$key.'&sesskey='.$sesskey.'">Edit</a></td></tr>';
    }
    
}
?>
</tbody>
</table>
<?php
$pinyinData = $DB->get_records_sql("SELECT c.recordid, c.content, (SELECT c2.content FROM {data_content} AS c2 WHERE c2.recordid = c.recordid AND c2.fieldid = 65) AS word_cnt, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 67 AND r.dataid = 9 order by c.recordid");
?>
<h2><ul>Point 2</ul></h2>
<table id="wordpractice" class="table table-bordered table-striped">    
<thead>
<tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>
</thead>
<tbody>
<?php
foreach($pinyinData as $key => $value){
    $wordcnt = $value->word_cnt;
    $pinyin = $value->pinyin;
    $pinyinSpaceCnt = substr_count($pinyin, ' ');
    $pinyinSpaceCnt = $pinyinSpaceCnt + 1;
    if($wordcnt != $pinyinSpaceCnt){
        echo '<tr><td>'.$value->content.'</td><td>'.$value->pinyin.' ('.$wordcnt.')('.$pinyinSpaceCnt.')</td><td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$key.'&sesskey='.$sesskey.'">Edit</a></td></tr>';
    }
    
}
?>
</tbody>
</table>