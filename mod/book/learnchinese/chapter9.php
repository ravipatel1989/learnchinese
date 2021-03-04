<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$sesskey = sesskey();
if(isset($_GET['list']) && $_GET['list']!=""){
    $recordid = filter_input(INPUT_GET, 'list');
    $sql = "SELECT c.recordid, c.content AS hskchar, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 49 AND c1.recordid = $recordid) AS pinyin FROM {data_content} AS c WHERE c.fieldid = 47 AND c.recordid = $recordid";
    $hskdata = $DB->get_records_sql($sql);
   
    $hskchar = $hskdata[$recordid]->hskchar;
    $pinyin = $hskdata[$recordid]->pinyin;
    $pinyin = str_replace(' ', '', $pinyin);
    $sql = "SELECT c.recordid, c.content AS contentchar, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68 AND (c1.content LIKE '% $pinyin %' OR c1.content LIKE '$pinyin %' OR c1.content LIKE '% $pinyin')) AS pinyin FROM {data_content} AS c WHERE c.fieldid = 67 AND c.content LIKE '%$hskchar%'";
    $sentenceData = $DB->get_records_sql($sql);
    $sentenceData = array_filter($sentenceData, function ($var) {
        return ($var->pinyin != '');
    });
    
?>
<h3><label>Character: </label><?php echo $hskchar; ?></h3>
<h3><label>Pinyin: </label><?php echo $pinyin; ?></h3>
<h3><label>Total Count: </label><?php echo count($sentenceData); ?></h3>
<table id="mismatchpinyin" class="table table-bordered table-striped">    
<thead>
<tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>
</thead>
<tbody>
    <?php foreach ($sentenceData as $key =>$value){
        if($value->contentchar !="" && $value->pinyin!=""){
        ?>
    <tr>
        <td><?php echo $value->contentchar; ?></td>
        <td><?php echo $value->pinyin; ?></td>
        <td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid=<?php echo $key; ?>&sesskey=<?php echo $sesskey; ?>">Edit</a></td>
    </tr>
    <?php } } ?>
</tbody>
</table>
<?php    
}else{
?>

<form action="" method="post">
    <label for="sell"><strong>Select HSK</strong></label>
    <div class="form-group">
        <select class="form-control" name="hsk" onchange="this.form.submit()">
        <option value=""> - Select HSK - </option>
        <option value="1" <?php if(isset($_POST) && $_POST['hsk']==1){ echo ' selected '; }?>>1</option>
        <option value="2" <?php if(isset($_POST) && $_POST['hsk']==2){ echo ' selected '; }?>>2</option>
        <option value="3" <?php if(isset($_POST) && $_POST['hsk']==3){ echo ' selected '; }?>>3</option>
        <option value="4" <?php if(isset($_POST) && $_POST['hsk']==4){ echo ' selected '; }?>>4</option>
        <option value="5" <?php if(isset($_POST) && $_POST['hsk']==5){ echo ' selected '; }?>>5</option>
        <option value="6" <?php if(isset($_POST) && $_POST['hsk']==6){ echo ' selected '; }?>>6</option>
        <option value="8" <?php if(isset($_POST) && $_POST['hsk']==8){ echo ' selected '; }?>>8</option>
        <option value="9" <?php if(isset($_POST) && $_POST['hsk']==9){ echo ' selected '; }?>>9</option>
    </select>
    </div>
</form>

<?php
if(isset($_POST['hsk'])){
    $hsk = $_POST['hsk'];
//    $sql = "SELECT c.recordid, c.content AS hskcharacter, (SELECT c3.content FROM {data_content} AS c3 WHERE c3.fieldid = 49 AND c3.recordid = c.recordid) AS hskpinyin, (SELECT count(c2.id) FROM {data_content} AS c2 WHERE (c2.fieldid = 67 AND c2.content) AND c2.content LIKE CONCAT('%',c.content ,'%')) AS countchar FROM {data_content} as c ORDER BY `countchar` DESC";
    
    $sql = "SELECT c.recordid, c.content AS hskchar, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 49) AS pinyin FROM {data_content} AS c WHERE c.fieldid = 47 AND c.recordid IN (SELECT c3.recordid FROM {data_content} as c3 where c3.fieldid = 48 AND c3.content = $hsk)";
    $hskData = $DB->get_records_sql($sql);
    
    $sql = "SELECT c.recordid, c.content AS sentence, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 68) AS pinyin FROM {data_content} AS c WHERE c.fieldid = 67";
    $sentenceData = $DB->get_records_sql($sql);
    $countArr = array();
    foreach($hskData as $key => $value){
        foreach($sentenceData as $skey => $svalue){
            $pinyinArr = array();
            $hskpinyin = str_replace(' ', '', $value->pinyin);
            $pinyinArr = explode(' ', $svalue->pinyin);
            if(strpos($svalue->sentence, $value->hskchar) !== false && in_array($hskpinyin, $pinyinArr)){
             $countArr[$key]++;   
            }
        }
    }

?>
<table id="mismatchpinyin" class="table table-bordered table-striped">    
<thead>
<tr><th>Character</th><th>Pinyin</th><th>Records</th><th>Action</th></tr>
</thead>
<tbody>
    <?php foreach($hskData as $key=>$value){?>
    <tr>
        <td><?php echo $value->hskchar; ?></td><td><?php echo $value->pinyin; ?></td><td><?php echo intval($countArr[$key]); ?></td><td><a target="_blank" class="btn btn-primary" href="view.php?id=15&chapterid=66&list=<?php echo $value->recordid; ?>">View list</a></td>
    </tr>
    <?php } ?>
</tbody>
</table>
<?php
}
}
?>
