<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $SESSION;
if(!isset($_POST['keyword'])){
?>
<div class="container">
    <form name="" action="" method="post">
        <div class="row">
            <div class="col-sm-12 col-md-7">
                <div class="form-group">
                    <input type="text" name="keyword" class="form-control" required="required" placeholder="Enter Keyword" />
                </div>
            </div>
            <div class="col-sm-12 col-md-5">
                <div class="form-group">
                    <select name="hsk" class="form-control" required="required">
                        <option value="">-Select HSK-</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 align-right">
                <input type="submit" class="btn btn-success" value="Search" />
            </div>
        </div>
    </form>
</div>
<?php } else { 
    $keyword = $SESSION->keyword = filter_input(INPUT_POST, 'keyword');
    $hsk = $SESSION->hsk = filter_input(INPUT_POST, 'hsk');
    $keywordSql = "SELECT recordid FROM {data_content} WHERE content LIKE '%$keyword%' AND fieldid = 58";
    $sql = "SELECT c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.recordid IN ($keywordSql)";
    $getData = $DB->get_records_sql($sql);
    $getData = array_values($getData);
    $getData = array_chunk($getData, 5);
    shuffle($getData);
    $goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid'=>16]);
    if(count($getData) > 0){
?>
<div class="align-right">
    <a href="<?php echo $goback; ?>" class="btn btn-primary" role="button" style="margin-bottom:5px;">Go back</a>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>English</th>
            <th>Word</th>
            <th>Sentence</th>
        </tr>
    </thead>
    <tbody>
<?php  
$cnt = 0;
foreach($getData as $key => $value){
    if(strpos($value[4]->content, $hsk) === false){
        continue;
    }
    $hskArr = str_split($value[4]->content);
    $newNumbers = array_filter(
        $hskArr,
        function ($value) use($hsk) {
            return ($value > $hsk);
        }
    );
    if(empty($newNumbers)){
        $cnt++;
        echo '<tr>';
        echo '<td>'.$cnt.'</td>';
        echo '<td>'.$value[3]->content.'</td>';
        echo '<td><a href="'.$goback.'&recordid='.$value[0]->recordid.'">'.$value[1]->content.'</a></td>';
        echo '<td>'.$value[2]->content.'</td>';
        echo '</tr>';
    }
}
?>      
    </tbody>
</table>
<?php 
    }
    if($cnt == 0){
        echo '<div class="align-center">';
        echo '<a href="'.$goback.'">No record found. Please try again.</a>';
        echo '</div>';
    }
} ?>
