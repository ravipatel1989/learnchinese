jQuery(document).ready(function($){
    init();
    var initialgroups = $("#IFgroups").data('initial');

    if(initialgroups !== undefined){
        var initialgroupsArr = initialgroups.split(',');
        var hideafter = parseInt(initialgroupsArr[0]);
    }
    
    var finalgroups = $("#IFgroups").data('final');
    if(finalgroups !== undefined){
        var finalgroupsArr = finalgroups.split(',');
        var hidebelow = parseInt(finalgroupsArr[0]);
    }
    
    var cnt = 0;

    $('#pinyinworksheet thead tr th').each(function(){
        cnt++;
        if(cnt > hideafter){
            if(!$(this).hasClass('nohidden')){
                $(this).addClass('hidden');
            }
        }
    });
    var tr = 1;
    $('#pinyinworksheet tbody tr').each(function(){
        var cnt = 0;
        tr++;
        if(tr > hidebelow){
            if(!$(this).hasClass('nohidden')){
                $(this).addClass('hidden');
            }
        }
        $(this).find('td').each(function(){
            cnt++;
            if(cnt > hideafter){
                if(!$(this).hasClass('nohidden')){
                    $(this).addClass('hidden');
                }
            }
        });
    });
    
    $("#pinyinworksheet #pinyinleft").on('click',function(){
        var thirdhidden = $('#pinyinworksheet thead tr th:nth-child(3)').hasClass('hidden');
        if(!thirdhidden){
            return false;
        }
        $('#pinyinworksheet thead tr th:not(.nohidden)').addClass('hidden');
        $('#pinyinworksheet tbody tr td:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(initialgroupsArr[parseInt(num) - 2]);
        start = parseInt(start) + parseInt(1);
        start = start || 0;
        var end = parseInt(initialgroupsArr[parseInt(num) - 1]);
        for(var i=start; i<=end; i++){
            $('#pinyinworksheet thead tr th:nth-child('+i+')').removeClass('hidden');
            $('#pinyinworksheet tbody tr td:nth-child('+i+')').removeClass('hidden');            
        }
        $("#pinyinright").attr('data-num',num);
        if($("#playinitial").length > 0){
            $("#playinitial").attr('data-num',num);
        }
        num--;
        $(this).attr('data-num',num);
    });
    $("#pinyinworksheet #pinyinright").on('click',function(){
        var secondlasthidden = $('#pinyinworksheet thead tr th:nth-last-child(2)').hasClass('hidden');
        if(!secondlasthidden){
            return false;
        }
        $('#pinyinworksheet thead tr th:not(.nohidden)').addClass('hidden');
        $('#pinyinworksheet tbody tr td:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(initialgroupsArr[parseInt(num) - 1]);
        start = parseInt(start) + parseInt(1);
        var end = parseInt(initialgroupsArr[parseInt(num)]);

        for(var i=start; i<=end; i++){
            $('#pinyinworksheet thead tr th:nth-child('+i+')').removeClass('hidden');
            $('#pinyinworksheet tbody tr td:nth-child('+i+')').removeClass('hidden');            
        }
        $("#pinyinleft").attr('data-num',num);
        num++;
        $(this).attr('data-num',num);
        if($("#playinitial").length > 0){
            $("#playinitial").attr('data-num',num);
        }
    });
    
    $("#pinyinworksheet #pinyindown").on('click',function(){
        var secondlasthidden = $('#pinyinworksheet tbody tr:nth-last-child(2)').hasClass('hidden');
        if(!secondlasthidden){
            return false;
        }
        $('#pinyinworksheet tbody tr:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(finalgroupsArr[parseInt(num) - 1]);
        start = parseInt(start);
        var end = parseInt(finalgroupsArr[parseInt(num)]);
        
        for(var i=start; i<end; i++){
            $('#pinyinworksheet tbody tr:nth-child('+i+')').removeClass('hidden');            
        }
        $("#pinyinup").attr('data-num',num);
        num++;
        $(this).attr('data-num',num);
        if($("#playfinal").length > 0){
            $("#playfinal").attr('data-num',num);
        }
        
    });
    $("#pinyinworksheet #pinyinup").on('click',function(){
        var thirdhidden = $('#pinyinworksheet tbody tr:nth-child(3)').hasClass('hidden');
        if(!thirdhidden){
            return false;
        }
        $('#pinyinworksheet tbody tr:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(finalgroupsArr[parseInt(num) - 2]);
        start = parseInt(start);
        start = start || 0;
        var end = parseInt(finalgroupsArr[parseInt(num) - 1]);
        for(var i=start; i<end; i++){
            $('#pinyinworksheet tbody tr:nth-child('+i+')').removeClass('hidden');            
        }
        $("#pinyindown").attr('data-num',num);
        if($("#playfinal").length > 0){
            $("#playfinal").attr('data-num',num);
        }
        num--;
        $(this).attr('data-num',num);
    });
    
    var uniquegroups = $("#Ugroups").data('unique');
    if(uniquegroups !== undefined){
        var uniquegroupsArr = uniquegroups.split(',');
        var hideafter = parseInt(uniquegroupsArr[0]);
    }
    
    $('#uniqueworksheet thead tr th').each(function(){
        cnt++;
        if(cnt > hideafter){
            if(!$(this).hasClass('nohidden')){
                $(this).addClass('hidden');
            }
            
        }
    });
    var tr = 0;
    $('#uniqueworksheet tbody tr').each(function(){
        var cnt = 0;
        $(this).find('td').each(function(){
            cnt++;
            if(cnt > hideafter){
                if(!$(this).hasClass('nohidden')){
                    $(this).addClass('hidden');
                }
            }
        });
    });
    $("#uniqueworksheet #pinyinright").on('click',function(){
        var secondlasthidden = $('#uniqueworksheet thead tr th:nth-last-child(2)').hasClass('hidden');
        if(!secondlasthidden){
            return false;
        }
        $('#uniqueworksheet thead tr th:not(.nohidden)').addClass('hidden');
        $('#uniqueworksheet tbody tr td:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(uniquegroupsArr[parseInt(num)]);
        start = parseInt(start) + parseInt(1);
        var end = parseInt(uniquegroupsArr[parseInt(num) + 1]);
        
        for(var i=start; i<=end; i++){
            $('#uniqueworksheet thead tr th:nth-child('+i+')').removeClass('hidden');
            $('#uniqueworksheet tbody tr td:nth-child('+i+')').removeClass('hidden');            
        }
        
        $("#pinyinleft").attr('data-num',num);
        num++;
        $(this).attr('data-num',num);
    });
    $("#uniqueworksheet #pinyinleft").on('click',function(){
        var secondhidden = $('#uniqueworksheet thead tr th:nth-child(2)').hasClass('hidden');
        if(!secondhidden){
            return false;
        }
        $('#uniqueworksheet thead tr th:not(.nohidden)').addClass('hidden');
        $('#uniqueworksheet tbody tr td:not(.nohidden)').addClass('hidden');
        
        var num = $(this).attr('data-num');
        var start = parseInt(uniquegroupsArr[parseInt(num) - 1]);
        start = parseInt(start) + parseInt(1);
        start = start || 0;
        var end = parseInt(uniquegroupsArr[parseInt(num)]);
        for(var i=start; i<=end; i++){
            $('#uniqueworksheet thead tr th:nth-child('+i+')').removeClass('hidden');
            $('#uniqueworksheet tbody tr td:nth-child('+i+')').removeClass('hidden');            
        }
        $("#pinyinright").attr('data-num',num);
        num--;
        $(this).attr('data-num',num);
    });
    $('.allcheckboxes').change(function(){ //".checkbox" change 
        if($('.allcheckboxes:checked').length == $('.checkbox').length){
               $('.sentencechk').prop('checked',false);
        }else{
               $('.sentencechk').prop('checked',true);
        }
    });
    // update pinyin in chapter 1.4
    $(document).on('click','.editpinyin',function(){
        var pinyinVal = $(this).closest("tr").find("td.pinyinval").text();
        $(this).closest("tr").find("td.pinyinval").html('<input id="pinyin" value="'+pinyinVal+'" />');
        $(this).text('Update').removeClass('btn-primary editpinyin').addClass('btn-success updatepinyin');
    });
    setTimeout(function(){
        $('.msgdiv').fadeOut();
    },3000);
    $('#radicalTbl').DataTable( {
        "order": [[ 9, "desc" ]],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'get_radicals'}    
        }
    } );
    $('#hsklevelwords').DataTable( {
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'hsklevelwords'},
            "dataSrc": function(json){
                var url = $('#updatehsk').data('url');
                var start = json.firstElement;
                var end = json.lastElement;
                url += '&start='+start+'&end='+end;
                $('#updatehsk').attr('href',url);
                return json.data;
            }
        }
    } );
    $('#keywordhsklevelwords').DataTable( {
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'keywordhsklevelwords'},
            "dataSrc": function(json){
                var url = $('#updatehsk').data('url');
                var start = json.firstElement;
                var end = json.lastElement;
                url += '&start='+start+'&end='+end;
                $('#updatehsk').attr('href',url);
                return json.data;
            }
        }
    } );
    $('#undefinedword').DataTable( {
        bFilter: false,
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'undefinedwordFn'},
            "dataSrc": function(json){
                return json.data;
            }
        }
    } );
    $('#senteceGID').DataTable( {
        bFilter: false,
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[10, 25, 50, 500, 1000], [10, 25, 50, 500, 1000]],
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'senteceGID'},
            "dataSrc": function(json){
                return json.data;
            }
        }
    } );
    
    $('#similarGID').DataTable( {
        bFilter: false,
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'similarkeywordFn'},
            "dataSrc": function(json){
                return json.data;
            }
        }
    } );
    
    $('#chhsklevelwords').DataTable( {
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax.php",
            "type": "POST",
            "data":{'action':'chhsklevelwords'},
            "dataSrc": function(json){
                var url = $('#updatechhsk').data('url');
                var start = json.firstElement;
                var end = json.lastElement;
                url += '&start='+start+'&end='+end;
                $('#updatechhsk').attr('href',url);
                return json.data;
            }
        }
    } );
    
    $('#radicalSearch').DataTable({
        "ordering": false,
    });
    $('#wordpractice, #matchfiles1, #matchfiles2, #unmatchedpinyin, #missinghsk, #datatable').DataTable();
    $('#mismatchpinyin, #keywordTable, #phraseCntTable').DataTable({
//        "oSearch": { "bSmart": false, "bRegex": true }
    });
    $('#updatesentence').DataTable({
         "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
    
    $("#sortable").sortable({
        revert: true
    });
    $("#draggable").draggable({
        connectToSortable: "#sortable",
        revert: "invalid",
        start: function(){
            $('#sentencewithpinyin').hide();
        },
        stop: function() {
            setTimeout(function(){
                let realword = $("#completesentence").data('sentence');
                let chinesechar = '';
                $('ul#sortable li').each(function(){
                    chinesechar += $(this).text();
                });
                if(realword == chinesechar){
                    alert('Great Job !');
                    $('#sentencewithpinyin').show();
                }else{
                    alert('Uh-oh, please try again!')
                }

            },1000);
        }
    });
    $("ul, li").disableSelection();
    
    $(".chinesechardrag").draggable({revert: "invalid", axis: "x" });

    $(".droppable").droppable({
        drop: function (event, ui) {
            var totalDrag = $(this).attr('data-totalcount');
            totalDrag = parseInt(totalDrag) - parseInt(1);
            $(this).siblings().attr('data-totalcount',parseInt(totalDrag));
            var $this = $(this);
            var width = $this.width();
            var height = $this.height();
            var cntrLeft = (width / 2) - (ui.draggable.width() / 2);
            var cntrTop = (height / 2) - (ui.draggable.height() / 2);

            ui.draggable.position({
              my: "center",
              at: "center",
              of: $this,
              using: function(pos) {
                $(this).animate(pos, "slow", "linear");
              }
            });
            
            var tone = $(this).data('tone');
            
            if(tone == 'correct'){
                if(totalDrag == 0){
                    $(this).closest('.row').append('<div class="info alert alert-success"><h6>Correct&nbsp;<i class="fa fa-check"></i></h6></div>');
                    var prevtotal = $('.totalscore .scoreoutof').text();
                    var currenttotal = parseInt(prevtotal) + parseInt(1);
                    $('.totalscore .scoreoutof').text(currenttotal);

                    var prevscore = $('.totalscore .score').text();
                    var currentscore = parseInt(prevscore) + parseInt(1);
                    $('.totalscore .score').text(currentscore);
                    ui.draggable.draggable({disabled: true});
                }
                if(totalDrag > 0){
                    $(this).removeClass('ui-widget-header').addClass('alert alert-success').find('p').html('<i class="fa fa-check"></i>');
                }
                $(this).removeAttr('data-tone');
            }else{
                $(this).closest('.row').append('<div class="info alert alert-danger"><h6>Wrong&nbsp;<i class="fa fa-close"></i></h6></div>');
                $(this).siblings('.correct').removeClass('ui-widget-header').addClass('alert alert-success').find('p').html('<i class="fa fa-check"></i>');
                var prevtotal = $('.totalscore .scoreoutof').text();
                var currenttotal = parseInt(prevtotal) + parseInt(1);
                $('.totalscore .scoreoutof').text(currenttotal);
                ui.draggable.draggable({disabled: true});
            }

        }
    });

    $(".flashcardsubmit").on('click',function(){
        var rotate = $(this).data('rotate');
        var next = $(this).data('next');
        var alreadysubmitted = $(this).attr('data-submit');
        if(alreadysubmitted == 'submitted'){
            alert('Already given answer.');
            return false;
        }
        var eleId = $(this).data('id');
        var answer = $(this).data('answer');
        answer = answer.toLowerCase();
        var inputanswer = $('#answer_'+eleId).val();
        inputanswer = inputanswer.toLowerCase();
        if(inputanswer == ""){
            alert('Please enter the answer');
            return false;
        }
        $(this).attr('data-submit','submitted');
        $('#flip-box-inner-'+eleId).css('transform','rotateY(180deg)');

        var prevtotal = $('.totalscore .scoreoutof').text();
        var currenttotal = parseInt(prevtotal) + parseInt(1);
        $('.totalscore .scoreoutof').text(currenttotal);
        if(answer == inputanswer){
            $(this).closest('.flip-box').find('.correctanswer').fadeIn(800);
            var prevscore = $('.totalscore .score').text();
            var currentscore = parseInt(prevscore) + parseInt(1);
            $('.totalscore .score').text(currentscore);
        }else{
            $(this).closest('.flip-box').find('.wronganswer').fadeIn(800);
        }
        setTimeout(function(){
            if(rotate!="no"){
                $('#flip-box-inner-'+eleId).css('transform','rotateY(0deg)');
            }
        },6000);
        setTimeout(function(){
            if(next!="no"){
                $('#flashcard .next').trigger('click');
            }
        },7000);
    });
    
    $('.playvideoasaudio').on('click',function(){
        var videoid = $(this).attr('data-videoid');
        var vid = $("#video_"+videoid);
        vid[0].play();
    });
});
function touchHandler(event) {
    var touch = event.changedTouches[0];

    var simulatedEvent = document.createEvent("MouseEvent");
        simulatedEvent.initMouseEvent({
        touchstart: "mousedown",
        touchmove: "mousemove",
        touchend: "mouseup"
    }[event.type], true, true, window, 1,
        touch.screenX, touch.screenY,
        touch.clientX, touch.clientY, false,
        false, false, false, 0, null);

    touch.target.dispatchEvent(simulatedEvent);
    event.preventDefault();
}

function init() {
//    document.addEventListener("touchstart", touchHandler, true);
    document.addEventListener("touchmove", touchHandler, true);
//    document.addEventListener("touchend", touchHandler, true);
//    document.addEventListener("touchcancel", touchHandler, true);
}