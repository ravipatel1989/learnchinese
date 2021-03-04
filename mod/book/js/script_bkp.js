jQuery(document).ready(function($){

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
    $('#radicalSearch').DataTable({
        "ordering": false,
    });
    $('#wordpractice, #matchfiles1, #matchfiles2, #unmatchedpinyin').DataTable();
    
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
});