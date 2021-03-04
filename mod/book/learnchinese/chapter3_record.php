<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$recordid = filter_input(INPUT_GET, 'recordid');
$sql = "SELECT c.content AS chinesechar, (SELECT content FROM {data_content} WHERE fieldid = 48 AND recordid = $recordid) AS hsk, (SELECT content FROM {data_content} WHERE fieldid = 49 AND recordid = $recordid) AS pinyin, (SELECT content FROM {data_content} WHERE fieldid = 50 AND recordid = $recordid) AS description FROM {data_content} AS c WHERE fieldid = 47 AND recordid = $recordid";
$getData = array_values($DB->get_records_sql($sql));
preg_match_all('/./u', $getData[0]->chinesechar, $characterArr);
$chineseData = [];
foreach($characterArr[0] as $key => $value){
    $sql = "SELECT * FROM {data_content} WHERE recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 14 AND content LIKE '$value')";    
    $chineseSql = $DB->get_records_sql($sql);
    $chineseData[] = array_values($chineseSql);
}
?>
<div class="container wordrecord">
    <div class="row">
        <div class="col-sm-12">
            <div class="chinesecharacter">
                <div class="innerdiv">
                    <h1><?php echo $getData[0]->chinesechar;?></h1>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="subdiv">
                <hr>
                <h2><?php echo $getData[0]->chinesechar;?>&nbsp;in English</h2>
            </div>
            <div class="description">
                <h4><?php echo $getData[0]->pinyin; ?></h4>
                <?php 
                    $description = explode(';', $getData[0]->description);
                ?>
                <ul>
                    <?php foreach($description as $value){ ?>
                    <li><?php echo $value; ?></li>
                    <?php }?>
                </ul>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="subdiv">
                <hr>
                <h2>HSK Level</h2>
            </div>
            <div class="description">
                <h4>HSK&nbsp;<?php echo $getData[0]->hsk; ?></h4>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="subdiv">
                <hr>
                <h2>Characters</h2>
            </div>
            <div class="description">
                <?php 
                if(!empty($chineseData)):
                    echo '<ul>';
                    foreach($chineseData as $data){
                        echo '<li><span>'.$data[0]->content.'('.$data[8]->content.'): </span>'.$data[2]->content.'</li>';
                    }
                    echo '<ul>';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>