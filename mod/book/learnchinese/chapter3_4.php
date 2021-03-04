<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$pinyinData = $DB->get_records_sql("SELECT r.id, content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '28' AND r.dataid = 3");
$pinyinValues = [];
foreach ($pinyinData as $key => $value) {
    $pinyin = preg_replace('/[0-9]+/', '', $value->content);
    $content = explode(' ', $pinyin);
    $content = array_unique($content);
    $totalcontent = count($content);
    if (!empty($totalcontent)) {
        foreach ($content as $k => $v) {
            $pinyinValues[] = $v;
        }
    } else {
        $pinyinValues[] = $pinyin;
    }
}
$pinyincounts = array_count_values($pinyinValues);
unset($pinyinData);
$searchVal = array_rand($pinyincounts);
unset($pinyincounts);
$refresh = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 12]);
?>
<div class="align-right">
    <a href="<?php echo $refresh; ?>" class="btn btn-primary" role="button" style=" margin-bottom:5px;">Try other</a>   
</div>
<?php
echo '<h2><center>' . $searchVal . '</center></h2>';
$pinyinData = $DB->get_records_sql("SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select RIGHT(content, 1) FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS tonenumber FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE (c.content LIKE '" . $searchVal . "1%' OR c.content LIKE '" . $searchVal . "2%' OR c.content LIKE '" . $searchVal . "3%' OR c.content LIKE '" . $searchVal . "4%' OR c.content LIKE '" . $searchVal . "5%' OR c.content LIKE '% " . $searchVal . "1%' OR c.content LIKE '% " . $searchVal . "2%' OR c.content LIKE '% " . $searchVal . "3%' OR c.content LIKE '% " . $searchVal . "4%' OR c.content LIKE '% " . $searchVal . "5%') AND c.fieldid = 28 AND r.dataid = 3");
echo '<div class="container">';
shuffle($pinyinData);
//$pinyinData = array_slice($pinyinData, 0, 8);  
foreach ($pinyinData as $key => $value) {
    $char1 = $searchVal.'1';
    $char2 = $searchVal.'2';
    $char3 = $searchVal.'3';
    $char4 = $searchVal.'4';
    $char5 = $searchVal.'5';
    $totalDrag = 0;
    for($i=1;$i<=5;$i++){
        if(strpos($value->content, $searchVal.$i) !== false){
            $totalDrag++;
        }    
    }
    
    ?>
    <div class="row">
        <div id="chinesechardrag" class="chinesechardrag ui-widget-content">
            <p><?php echo $value->charcontent; ?></p>
        </div>
        <div id="chinesedrop1" class="droppable ui-widget-header <?php if($totalDrag > 1){ echo 'more'; } ?> <?php if(strpos($value->content, $char1) !== false){ echo 'correct'; } ?>" <?php if(strpos($value->content, $char1) !== false){ echo 'data-tone="correct"'; } ?> data-totalcount="<?php echo $totalDrag; ?>">
            <p>1</p>
        </div>
        <div id="chinesedrop2" class="droppable ui-widget-header <?php if($totalDrag > 1){ echo 'more'; } ?> <?php if(strpos($value->content, $char2) !== false){ echo 'correct'; } ?>" <?php if(strpos($value->content, $char2) !== false){ echo 'data-tone="correct"'; } ?> data-totalcount="<?php echo $totalDrag; ?>">
            <p>2</p>
        </div>
        <div id="chinesedrop3" class="droppable ui-widget-header <?php if($totalDrag > 1){ echo 'more'; } ?> <?php if(strpos($value->content, $char3) !== false){ echo 'correct'; } ?>" <?php if(strpos($value->content, $char3) !== false){ echo 'data-tone="correct"'; } ?> data-totalcount="<?php echo $totalDrag; ?>">
            <p>3</p>
        </div>
        <div id="chinesedrop4" class="droppable ui-widget-header <?php if($totalDrag > 1){ echo 'more'; } ?> <?php if(strpos($value->content, $char4) !== false){ echo 'correct'; } ?>" <?php if(strpos($value->content, $char4) !== false){ echo 'data-tone="correct"'; } ?> data-totalcount="<?php echo $totalDrag; ?>">
            <p>4</p>
        </div>
        <div id="chinesedrop5" class="droppable ui-widget-header <?php if($totalDrag > 1){ echo 'more'; } ?> <?php if(strpos($value->content, $char5) !== false){ echo 'correct'; } ?>" <?php if(strpos($value->content, $char5) !== false){ echo 'data-tone="correct"'; } ?> data-totalcount="<?php echo $totalDrag; ?>">
            <p>5</p>
        </div>
    </div>
    <?php
}

echo '</div>';
?>
<div class="totalscore" style="margin-top:10px;">
    <h3>Score <span class="score">0</span> out of <span class="scoreoutof">0</span></h3>
</div>
