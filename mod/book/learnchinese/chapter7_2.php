<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$sesskey = sesskey();

$sql = "SELECT recordid, content AS pinyin, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) AS chchar FROM {data_content} AS c WHERE fieldid = 68 AND content LIKE '%5%'";
$records = $DB->get_records_sql($sql);
?>
<a href="https://learnchinese.center/mod/data/edit.php?d=9" class="btn btn-primary" target="_blank" >Add New</a>
<table id="datatable" class="table table-bordered table-striped">    
        <thead>
            <tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
                foreach ($records as $record){
                    
                    $explodepinyin =  preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $record->chchar);
                    $explodepinyin = mb_str_split($explodepinyin);
                    $sentenceAddPipe = str_replace(array('1','2','3','4','5'), array('1|','2|','3|','4|','5|'), $record->pinyin);
                    $sentenceAddPipe = rtrim($sentenceAddPipe,'|');
                    $sentenceArr = explode('|', $sentenceAddPipe);
                    
                    // Convert every value to uppercase, and remove duplicate values
//                    $withoutDuplicates = array_unique($explodepinyin);
                    
                    if(count($explodepinyin) !== count(array_unique($explodepinyin))){
                        $match = false;
                        $pos = 0;
                        for($i=1;$i<count($explodepinyin);$i++){
                            $j = $i-1;
                            if($explodepinyin[$i]==$explodepinyin[$j] && (strpos($sentenceArr[$i], '5') !== false || strpos($sentenceArr[$j], '5'))){
                                $match = true;
                                $pos = $i;
                                break;
                            }
                        }
                        if($match == true){
                            echo '<tr>';
                            echo '<td>'.$record->chchar.'</td>';
                            echo '<td>'.$record->pinyin.'</td>';
                            echo '<td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$record->recordid.'&sesskey='.$sesskey.'">Edit</a></td>';
                            echo '</tr>';
                        }
                    }
                }
                
                function mb_str_split( $string ) {

                    return preg_split('/(?<!^)(?!$)/u', $string );
                }
            ?>
        </tbody>
</table>
            