<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE;
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/custom.js') );
if(isset($_GET['hsk']) && $_GET['hsk']!=""){
    $hsk = filter_input(INPUT_GET, 'hsk');
    $flashcardData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE recordid IN (SELECT c1.recordid FROM {data_content} as c1 WHERE c1.fieldid = 25 AND c1.content <= $hsk) AND (c.fieldid = '14' OR c.fieldid = '22') AND r.dataid = 3");

    $flashcardData = array_chunk($flashcardData, 2);
    shuffle($flashcardData);
    $totalRec = count($flashcardData);
    $goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 11]);
    ?>
    <div class="align-right">
    <a href="<?php echo $goback; ?>" class="btn btn-info" role="button" style=" margin-bottom:5px;">Go back</a>   
    </div>
    <div id="flashcard" class="slideshow-container">
        <?php
        foreach ($flashcardData as $key => $value) {
            $randomNumber = rand(0,1);
            $answer = 'yes';
            $back = $value[1]->content;
            if($randomNumber == 1){
                $random_key = rand(0,$totalRec);
                if($random_key != $key){
                    $answer = 'no';
                    $back = $flashcardData[$random_key][1]->content;
                }
            }
            ?>
            <div class="flip-box form-group mySlides fadeslide">
                <div class="correctanswer"><span>Correct&nbsp;<i class="fa fa-check"></i></span></div>
                <div class="wronganswer"><span>Wrong&nbsp;<i class="fa fa-close"></i></span></div>
                <div id="flip-box-inner-<?php echo $key; ?>" class="flip-box-inner" data-randomkey="<?php echo $random_key; ?>" data-key="<?php echo $key; ?>">
                    <div class="flip-box-front normalfront">
                        <p class="chinesechar"><?php echo $value[0]->content; ?></p>
                        <p><?php echo $back; ?></p>
                    </div>
                    <div class="flip-box-back normalback">
                        <p class="chinesechar"><?php echo $value[0]->content; ?></p>
                        <p><?php echo $value[1]->content; ?></p>
                    </div>
                </div>
                <div class="align-center">
                    <input type="hidden" id="answer_<?php echo $key; ?>" value="<?php echo $answer; ?>" />
                    <label class="radio radio-inline">
                        <input type="radio" name="answer" class="flashcardsubmit" data-id="<?php echo $key; ?>" <?php if($answer == 'yes'){ echo 'data-answer="yes"';} ?>>&nbsp;Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <label class="radio radio-inline">
                        <input type="radio" name="answer" class="flashcardsubmit" data-id="<?php echo $key; ?>" <?php if($answer == 'no'){ echo 'data-answer="no"';} ?>>&nbsp;No</label>
                </div>
            </div>

        <?php } ?>
        <!-- Next and previous buttons -->
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <div class="totalscore">
        <h3>Score <span class="score">0</span> out of <span class="scoreoutof">0</span></h3>
    </div>
<?php   
}else{
    $hsklist = $DB->get_records_sql("SELECT DISTINCT(c.content) AS hsk FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE c.fieldid = 25 AND r.dataid = 3");
?>
<div class="container">
    <form action="" method="get">
    <input type="hidden" name="id" value="15" />
    <input type="hidden" name="chapterid" value="11" />
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <select name="hsk" class="form-control" required="required">
                    <option value="">- Select HSK level -</option>
                    <?php foreach($hsklist as $value) : ?>
                    <option value="<?php echo $value->hsk; ?>"><?php echo $value->hsk; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit" class="btn btn-success" />
            </div>
        </div>
    </div>
    </form>
</div>
<?php    
}
?>
