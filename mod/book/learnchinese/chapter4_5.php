<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$pinyinData = $DB->get_records_sql('SELECT r.id, c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 1');
$pinyinArr = $initialArr = $finalArr = $uniqueArr = array();
foreach($pinyinData as $id => $record) {
    if($record->fieldid == '9'){
        $pinyinArr[$record->recordid]['mp3id'] = $record->id;
    }
    $pinyinArr[$record->recordid][$record->fieldid] = $record->content;
}
usort($pinyinArr, function($a, $b) {
    return $a[21] <=> $b[21];
});
$initialGroupCnt = $finalGroupCnt = [];
$initialCnt = $finalCnt = 2;
foreach($pinyinArr as $key => $value){
    if($value[3] == 'I'){
        $initialArr[] = $value;
        $initialGroupCnt[$value[8]] = ++$initialCnt;
    }
    if($value[3] == 'F'){
        $finalArr[] = $value;
        $finalGroupCnt[$value[8]] = ++$finalCnt;
    }
    if($value[3] == 'U'){
        $uniqueArr[] = $value;
    }
}

$lastEle = array_key_last($initialGroupCnt);
$initialGroupCnt[$lastEle] = $initialGroupCnt[$lastEle] + 1; 
$uniqueArrColumns = array_column($uniqueArr, 1);
?>
<table id="pinyinworksheet" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th scope="col" class="nohidden" width="50"><span id="IFgroups" class="hidden" data-initial="<?php echo implode(',', $initialGroupCnt); ?>" data-final="<?php echo implode(',', $finalGroupCnt); ?>"></span></th>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="0" id="pinyinleft"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></th>
            <?php 
                foreach($initialArr as $key => $value){
                    echo '<th scope="col" width="50">'.$value[1].'</th>';
                }
            ?>
            <th scope="col" width="50">∅</th>
            <th scope="col" class="nohidden" width="50"><a href="javascript:void(0);" data-num="1" id="pinyinright"><i class="fa fa-arrow-right" aria-hidden="true"></i></a></th>
        </tr>
    </thead>
    <tbody>
        <tr class="nohidden">
            <td class="nohidden"><a href="javascript:void(0);" data-num="0" id="pinyinup"><i class="fa fa-arrow-up" aria-hidden="true"></i></a></td>
            <td class="nohidden"></td>
            <?php foreach($initialArr as $key => $value){
                    if(!isset($value[60]) || $value[60]==""){
                        echo '<td><b>'.$value[2].'</b></td>';
                    }else{
                        echo '<td><div class="tooltipcustom"><b>'.$value[2].'</b><span class="tooltiptext">'.$value[60].'</span></div></td>';
                    }
                }
            ?>
            <td></td>
            <td class="nohidden"><a href="javascript:PlayInitial()" data-groups="<?php echo implode(',', array_keys($initialGroupCnt)); ?>" id="playinitial" data-num="1"><i class="fa fa-play"></i></a></td>
        </tr>
        <?php foreach($finalArr as $key => $value){
            echo '<tr>';
                echo '<td class="nohidden"><b>'.$value[1].'</b></td>';
                if(!isset($value[60]) || $value[60]==""){
                    echo '<td class="nohidden"><b>'.$value[2].'</b></td>';
                }else{
                    echo '<td class="nohidden"><div class="tooltipcustom"><b>'.$value[2].'</b><span class="tooltiptext">'.$value[60].'</span></div></td>';
                }
                for($i=0;$i<24;$i++){
                    $finalVal = $finalArr[$key][1];
                    if($finalArr[$key][1] == "u" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ü";
                    }
                    if($finalArr[$key][1] == "ü" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "u";
                    }
                    if($finalArr[$key][1] == "üe" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ue";
                    }
                    if($finalArr[$key][1] == "ue" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "üe";
                    }
                    if($finalArr[$key][1] == "ün" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "un";
                    }
                    if($finalArr[$key][1] == "un" && in_array($initialArr[$i][1], array('j','q','x','y'))){
                        $finalVal = "ün";
                    }
                    $arrval = $initialArr[$i][1].$finalVal;
                    if(isset($arrval) && in_array($arrval, $uniqueArrColumns)){ 
                        $keyvalue = array_search($arrval, $uniqueArrColumns); 
                        $uniqueChar = $uniqueArr[$keyvalue][8];
                        echo '<td><span style="color:red;">©</span>'.$uniqueChar.'</td>';
                    }else{
                        echo '<td></td>';   
                    }
                }

                echo '<td class="nohidden"></td>';
            echo '</tr>';
        }
        ?>
        <?php for($i=0;$i<2;$i++){ ?>
        <tr class="nohidden">
            <td class="nohidden"><?php if($i==0){ ?><a href="javascript:void(0);" data-num="1" id="pinyindown"><i class="fa fa-arrow-down" aria-hidden="true"></i></a><?php }else{ ?><br><?php } ?></td>
            <td class="nohidden"><a href="javascript:PlayFinal()" data-groups="<?php echo implode(',', array_keys($finalGroupCnt)); ?>" id="playfinal" data-num="1"><i class="fa fa-play"></i></a></td>
            <?php
            for($i=0;$i<24;$i++){
                echo '<td></td>';
            }
            ?>
            <td class="nohidden"></td>
        </tr>    
        <?php }?>
    </tbody>
</table>
<script>
        function PlayInitial() {
            var group = document.getElementById('playinitial').getAttribute("data-num");
            var groupnumbers = document.getElementById('playinitial').getAttribute("data-groups");
            var groupArray = groupnumbers.split(",");
            group = parseInt(group) - parseInt(1);
            group = groupArray[group];
            var initialArray = [];
            <?php foreach ($initialArr as $key => $value) : ?>
            if(group == "<?php echo $value[8]; ?>"){
                initialArray.push("<?php echo $CFG->wwwroot; ?>/pluginfile.php/40/mod_data/content/<?php echo $value['mp3id']; ?>/<?php echo $value[9]; ?>");
            
            }
            <?php endforeach; ?>
            
            var audio = new Audio();
            audio.src = initialArray[0];

            if (initialArray.length > 0) {
                audio.play();
            }

            var i = 0;

            audio.addEventListener('ended', function () {
                i++;
                if (i < initialArray.length) {
                    audio.src = initialArray[i];
                    audio.play();
                }
            }, false);
        }
        function PlayFinal() {
            var group = document.getElementById('playfinal').getAttribute("data-num");
            var groupnumbers = document.getElementById('playfinal').getAttribute("data-groups");
            var groupArray = groupnumbers.split(",");
            group = parseInt(group) - parseInt(1);
            group = groupArray[group];
            var finalArray = [];

            <?php foreach ($finalArr as $key => $value) : ?>
            if(group == "<?php echo $value[8]; ?>"){
                finalArray.push("<?php echo $CFG->wwwroot; ?>/pluginfile.php/40/mod_data/content/<?php echo $value['mp3id']; ?>/<?php echo $value[9]; ?>");
            
            }
            <?php endforeach; ?>
            
            var audio = new Audio();
            audio.src = finalArray[0];

            if (finalArray.length > 0) {
                audio.play();
            }

            var i = 0;

            audio.addEventListener('ended', function () {
                i++;
                if (i < finalArray.length) {
                    audio.src = finalArray[i];
                    audio.play();
                }
            }, false);
        }
</script>