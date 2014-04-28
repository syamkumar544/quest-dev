$(document).ready(function() {  
setTimeout( "$('#empty').hide();", 4000);  
$('#btnRight').click(function(e) {   
var selectedOpts = $('#lstBox1 option:selected');
        if (selectedOpts.length == 0) {
            alert("Nothing to Add.");
            e.preventDefault();
        }

        $('#lstBox2').append($(selectedOpts).clone());
        $("#lstBox2 option").attr("selected","selected");
        $(selectedOpts).remove();
        e.preventDefault();
        $('#lstBox2 option').each(function(i) {
           $(this).attr("selected", "selected");
        });
});
$('#btnLeft').click(function(e) {
var selectedOpts = $('#lstBox2 option:selected');
        if (selectedOpts.length == 0) {
            alert("Nothing to Remove.");
            e.preventDefault();
        }

        $('#lstBox1').append($(selectedOpts).clone());
        $(selectedOpts).remove();
        e.preventDefault();
});

$('#id_category_id').change(function(e) {
   var category = $(this).val();
   location = 'category.php?category_id='+category;
});

});

