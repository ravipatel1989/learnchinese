<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
if(isset($_POST) && $_POST['action']=="deleteallgid"){
    $sql = "UPDATE {data_content} SET content = '' WHERE fieldid = 106";
    $DB->execute($sql);
}
?>
<form action="" method="post">
    <div class="align-right" style="display:block">
        <input type="hidden" name="action" value="deleteallgid" />
        <input type="submit" name="deletegid" value="Delete GID" class="btn btn-primary" onclick="return confirm('Are you sure to delete all GID ?')" />
    </div>
</form>
<table id="senteceGID" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Sentence</th>
            <th>GID</th>
            <th>Action</th>
        </tr>
    </thead>
</table>