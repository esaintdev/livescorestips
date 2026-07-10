$(document).on('click', '.date-toggle', function () {
   var id = $(this).data('id');
   // handle active state
    $('.date-toggle').removeClass('active');
    $('.tip-wrapper-inner').removeClass('active');
    $('.tip-wrapper-inner').hide();
    $(this).addClass('active');
    $('#'+id).fadeIn();
});

$(document).on('click', '.dropdown-date-toggle', function (e) {
    e.preventDefault();

   var id = $(this).data('id');
   var text = $(this).text();
   $('#dropdownMenuLink').text(text);

   // handle active state
    $('.dropdown-date-toggle').removeClass('active');
    $('.tip-wrapper-inner').removeClass('active');
    $('.tip-wrapper-inner').hide();
    $(this).addClass('active');
    $('#'+id).fadeIn();
});