<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php if(!isset($_GET['search']) || $_GET['search'] == ""){ ?>
            <form action="" method="GET">
                <input type="hidden" name="id" value="15" />
                <input type="hidden" name="chapterid" value="7" />
                <div class="form-group">
                    <input type="text" class="form-control" name="search" id="search" aria-describedby="searchHelp" placeholder="Enter the keyword">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <?php }else{ 
                $search = filter_input(INPUT_GET, 'search');
                $sql = "SELECT c.recordid, c.content AS meaning, (select content FROM {data_content} as ct WHERE ct.fieldid = 47 AND ct.recordid = c.recordid) AS characterval, (select content FROM {data_content} as chsk WHERE chsk.fieldid = 48 AND chsk.recordid = c.recordid) AS hsk, (select content FROM {data_content} as cpy WHERE cpy.fieldid = 49 AND cpy.recordid = c.recordid) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.content LIKE '%$search%' AND c.fieldid = 50 AND r.dataid = 5";
                $pinyinData = array_values($DB->get_records_sql($sql));
                $linkurl = new moodle_url('view.php',['id'=>15, 'chapterid'=>7]);
                if(!empty($pinyinData)){
                    echo '<table id="wordpractice" class="table table-bordered table-striped">';
                    echo '<thead>';
                        echo '<tr>';
                            echo '<th>#</th>';
                            echo '<th>Character</th>';
                            echo '<th>HSK</th>';
                            echo '<th>Pinyin</th>';
                            echo '<th>Meaning</th>';
                        echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    foreach($pinyinData as $key => $value){
                        echo '<tr>';
                        echo '<td>'.($key + 1).'</td>';
                        echo '<td><a href="'.$linkurl.'&recordid='.$value->recordid.'" target="_blank">'.$value->characterval.'</a></td>';
                        echo '<td>'.$value->hsk.'</td>';
                        echo '<td>'.$value->pinyin.'</td>';
                        echo '<td>'.$value->meaning.'</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                }
                
            ?>
            
            <?php } ?>
        </div>
    </div>
</div>