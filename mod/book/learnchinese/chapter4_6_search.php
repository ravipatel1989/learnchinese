<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$searchVal = filter_input(INPUT_GET, 'search');
$pinyinData = $DB->get_records_sql("SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select RIGHT(content, 1) FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS tonenumber,(select content FROM {data_content} AS tn WHERE tn.fieldid = 29 AND tn.recordid = c.recordid) AS multiplepinyin FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE (c.content LIKE '".$searchVal."1%' OR c.content LIKE '".$searchVal."2%' OR c.content LIKE '".$searchVal."3%' OR c.content LIKE '".$searchVal."4%' OR c.content LIKE '".$searchVal."5%' OR c.content LIKE '% ".$searchVal."1%' OR c.content LIKE '% ".$searchVal."2%' OR c.content LIKE '% ".$searchVal."3%' OR c.content LIKE '% ".$searchVal."4%' OR c.content LIKE '% ".$searchVal."5%') AND c.fieldid = 28 AND r.dataid = 3");
$pinyinArr = [];
$i = 0;
foreach($pinyinData as $key => $value){
    if (strpos($value->content, $searchVal) !== false) {
        $content = explode(' ', $value->content);
        if(intval($value->multiplepinyin) < 2){
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
}
ksort($pinyinArr);
$goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 22]); 
?>
<div class="align-right">
    <button class="btn btn-success" id="playbutton" onclick="playvideos()" style=" margin-bottom:5px; float: left;">Play&nbsp;&nbsp;<i class="fa fa-play"></i></button>   
<a href="<?php echo $goback; ?>" class="btn btn-info" role="button" style=" margin-bottom:5px;">Go back</a>   
</div>
<div class="clear"></div>
<h1><center><?php echo $searchVal; ?></center></h1>
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
        $videoArr = [];
        $cnt = 0;
//        for($i=0;$i<$maxlen;$i++){
        for($i=0;$i<1;$i++){
            echo '<tr>';
            for($j=1;$j<=5;$j++){
                $cnt++;
                if(isset($pinyinArr[$j][$i]['charcontent'])){
                    $chineseChar = $pinyinArr[$j][$i]['charcontent'];
                    $mp4file = $CFG->wwwroot.'/characterdata/'.$chineseChar.'.mp4';
                    $filepath = $CFG->characterdata.'/'.$chineseChar.'.mp4';
                    if(!file_exists($filepath)){
                        $mp4file = $CFG->wwwroot.'/characterdata/'.$chineseChar.'-多音字.mp4';
                        $filepath = $CFG->characterdata.'/'.$chineseChar.'-多音字.mp4';
                    }
                    if(file_exists($filepath)){
                        $videoArr[] = array($cnt, $mp4file);
                        echo '<td><a href="javascript:void(0);" id="audiocharacter_'.$cnt.'" class="audiocharacter" >'.$chineseChar.'</a></td>';
                    }else{
                        echo '<td></td>';
                    }
                }else{
                    echo '<td></td>';
                }    
            }
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
<video width="0" height="0" id="myVid" controls playsinline>
    <source src="" type="video/mp4">
</video>
<script type="text/javascript">

var videoSource = new Array();
<?php foreach($videoArr as $video): ?>
    videoSource.push(["<?php echo $video[0]; ?>","<?php echo $video[1]; ?>"]);
<?php endforeach; ?>
    
var videoCount = videoSource.length;
function resetcharactersize(){
    var elems = document.querySelectorAll(".audiocharacter");
    [].forEach.call(elems, function(el) {
        el.classList.remove("activeele");
    });
}
function playArray(index,ele,array,listener){
    var stopVideo = index;
    document.getElementById("audiocharacter_"+videoSource[index][0]).classList.add('activeele');
    ele.src = array[index][1];
    ele.load();
    ele.play();
    index++;
    if(index>=array.length){
        stopVideo = 999;
    }
    listener = ele.addEventListener('ended',function(){
        resetcharactersize();
        if(stopVideo==999){
            ele.pause();
            return false;
        }
        playArray(index,ele,array,listener);
    },false);
}
function playvideos(){
    playArray(0,document.getElementById("myVid"),videoSource);
}

</script>