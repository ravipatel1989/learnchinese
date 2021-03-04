<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE;

$hskData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE fieldid = 47 OR fieldid = 49 AND r.dataid = 5 order by c.id");

$hskData = array_chunk($hskData,2);

?>
<table id="missinghsk" class="table table-bordered table-striped">
    <thead>
        <tr><th>Character</th><th>Pinyin</th></tr>
    </thead>
    <tbody>
<?php
foreach($hskData as $key => $value){
    $totalnumber = preg_match_all( "/[0-9]/", $value[1]->content );
    $totalnumber--;
    $spaceCnt = substr_count(trim($value[1]->content), ' ');
    if($spaceCnt < $totalnumber){
?>
    <tr>
        <td><?php echo $value[0]->content; ?></td>
        <td><?php echo $value[1]->content; ?></td>
    </tr>
<?php
        $str = $value[1]->content;
        if(strpos($str, '1') !== false){
            $str = str_replace('1', '1 ', $str);
        }
        if(strpos($str, '2') !== false){
            $str = str_replace('2', '2 ', $str);
        }
        if(strpos($str, '3') !== false){
            $str = str_replace('3', '3 ', $str);
        }
        if(strpos($str, '4') !== false){
            $str = str_replace('4', '4 ', $str);
        }
        if(strpos($str, '5') !== false){
            $str = str_replace('5', '5 ', $str);
        }
        $str = trim($str);
        $sql = "UPDATE {data_content} SET content = '$str' WHERE id = {$value[1]->id}";
        $update = $DB->execute($sql);
    }
}
?>
    </tbody>
</table>