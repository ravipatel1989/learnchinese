<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$searchVal = filter_input(INPUT_GET, 'search');
$pinyinData = $DB->get_records_sql("SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select RIGHT(content, 1) FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS tonenumber FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE (c.content LIKE '".$searchVal."1%' OR c.content LIKE '".$searchVal."2%' OR c.content LIKE '".$searchVal."3%' OR c.content LIKE '".$searchVal."4%' OR c.content LIKE '".$searchVal."5%' OR c.content LIKE '% ".$searchVal."1%' OR c.content LIKE '% ".$searchVal."2%' OR c.content LIKE '% ".$searchVal."3%' OR c.content LIKE '% ".$searchVal."4%' OR c.content LIKE '% ".$searchVal."5%') AND c.fieldid = 28 AND r.dataid = 3");

//echo '<pre>';
//print_r($pinyinData);

$pinyinArr = [];
$i = 0;
foreach($pinyinData as $key => $value){
    if (strpos($value->content, $searchVal) !== false) {
        $content = explode(' ', $value->content);
        if(!empty($content) && count($content) > 1){
            foreach($content as $k => $v){
                $pinyinchar = preg_replace('/[0-9]+/', '', $v);
                if ($pinyinchar == $searchVal) {
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
$goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 13]); 
?>
<div class="align-right">
<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="btn btn-info" role="button" style=" margin-bottom:5px;">Go back</a>   
</div>
<div class="clear"></div>
<h2><center><?php echo strtoupper($_GET['search']); ?></center></h2>
<table class="table table-bordered table-striped">
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
                    $chineseChar = $pinyinArr[$j][$i]['charcontent'];
                    $mp4file = $CFG->wwwroot.'/characterdata/'.$chineseChar.'.mp4';
                    if(!file_exists($CFG->characterdata.'/'.$chineseChar.'.mp4')){
                        $mp4file = $CFG->wwwroot.'/characterdata/'.$chineseChar.'-多音字.mp4';
                    }
                    echo '<td><a href="'.$mp4file.'" target="_blank">'.$chineseChar.'</a></td>';
                }else{
                    echo '<td></td>';
                }    
            }
            echo '</tr>';
        }
        ?>
    </tbody>
</table>