<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$sesskey = sesskey();
if($_GET['action']=="updatehsk"){
    $sentenceArr = $_POST['sentenceArr'];
    foreach($sentenceArr as $sentence){
        if(isset($_POST['sentence']) && $_POST['sentence']!=""){
            $oldsent = $_POST['oldsentence'];
            $sent = $_POST['sentence'];
            $sql = "UPDATE `{data_content}` SET `content` = REPLACE(content, '$oldsent', '$sent') WHERE `fieldid` = 67 AND `recordid` = '$sentence'";
            $update = $DB->execute($sql);
        }
        if(isset($_POST['pinyin']) && $_POST['pinyin']!=""){
            $oldpinyin = $_POST['oldpinyin'];
            $pinyin = $_POST['pinyin'];
            $sql = "UPDATE `{data_content}` SET `content` = REPLACE(content, '$oldpinyin', '$pinyin') WHERE `fieldid` = 68 AND `recordid` = '$sentence'";
            $update = $DB->execute($sql);
        }
        
    }
        
//                $update = $DB->execute($sql);
}
if($_GET['action']=="gethsk"){
    if($_POST['sentence']!="" && $_POST['pinyin']!=""){
        $pinyin = $_POST['pinyin'];
        $sentence = $_POST['sentence'];
        $sql = "SELECT c.recordid, c.content AS pinyin,(SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) AS sentence FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 68 AND c.content LIKE '%$pinyin%'";
        $sentenceData = $DB->get_records_sql($sql);
        foreach($sentenceData as $key => $value){
            if(strpos($value->pinyin, $pinyin) === false){
                unset($sentenceData[$key]);
            }
        }

    }
    if($_POST['sentence']!="" && $_POST['pinyin']==""){
        $sentence = $_POST['sentence'];
        $sql = "SELECT c.recordid, c.content AS sentence,(SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 68 AND c1.recordid = c.recordid) AS pinyin FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 67 AND c.content LIKE '%$sentence%'";
        $sentenceData = $DB->get_records_sql($sql);
    }
    if($_POST['sentence']=="" && $_POST['pinyin']!=""){
        $pinyin = $_POST['pinyin'];
        $sql = "SELECT c.recordid, c.content AS pinyin,(SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) AS sentence FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 68 AND c.content LIKE '%$pinyin%'";
        $sentenceData = $DB->get_records_sql($sql);
    }
if(isset($_POST['sentence']) && $_POST['sentence']!=""){
    echo '<h4>Sentence: '.$_POST['sentence'].'</h4>';
}
if(isset($_POST['pinyin']) && $_POST['pinyin']!=""){
    echo '<h4>Pinyin: '.$_POST['pinyin'].'</h4>';
}
$updatehsk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 70, 'action'=>'updatehsk']);
?>
<form name="updatesentence" action="<?php echo $updatehsk; ?>" method="post">
    <?php if(isset($_POST['sentence']) && $_POST['sentence']!=""){ ?>
    <input type="hidden" name="oldsentence" value="<?php echo $_POST['sentence']; ?>" />
<div class="form-group">
    <input type="text" class="form-control" name="sentence" id="sentence" style="max-width: 100%;" placeholder="Update sentence" required="required">
</div>
    <?php } ?>
    <?php if(isset($_POST['pinyin']) && $_POST['pinyin']!=""){ ?>
    <input type="hidden" name="oldpinyin" value="<?php echo $_POST['pinyin']; ?>" />
<div class="form-group">
    <input type="text" class="form-control" name="pinyin" id="pinyin" style="max-width: 100%;" placeholder="Update pinyin" required="required">
</div>
    <?php } ?>
<table id="updatesentence" class="table table-bordered table-striped">    
<thead>
    <tr><th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th><th>Character</th><th>Pinyin</th><th>Action</th></tr>
</thead>
<tbody>
    <?php
        foreach($sentenceData as $key => $sentence){
    ?>
    <tr>
        <td><input type="checkbox" name="sentenceArr[]" id="sentence_<?php echo $key;?>" class="sentencechk" checked="checked" value="<?php echo $key;?>" /></td>
        <td><?php echo $sentence->sentence; ?></td>
        <td><?php echo $sentence->pinyin; ?></td>
        <td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid=<?php echo $key; ?>&sesskey=<?php echo $sesskey; ?>">Edit</a></td>
    </tr>
    <?php
        }
    ?>
</tbody>
</table>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
<?php }else{ 
    $gethsk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 70, 'action'=>'gethsk']);
    ?>

<br><br>
<form action="<?php echo $gethsk; ?>" method="post">
  <div class="form-group">
      <input type="text" class="form-control" name="sentence" id="sentence" style="max-width: 100%;" placeholder="Enter sentence">
  </div>
  <div class="form-group">
        <input type="text" class="form-control" name="pinyin" id="pinyin" style="max-width: 100%;" placeholder="Enter pinyin">
  </div>
  
  <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?php } ?>
<script>
    jQuery(document).ready(function($){
        $('.allcheckboxes').change(function(){ //".checkbox" change 
            if($('.allcheckboxes:checked').length == $('.checkbox').length){
                   $('.sentencechk').prop('checked',false);
            }else{
                   $('.sentencechk').prop('checked',true);
            }
        });
    });
</script>