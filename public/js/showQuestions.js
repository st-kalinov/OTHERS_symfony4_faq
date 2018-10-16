$(document).ready(function () {
    $('.fa-thumbs-up').on('click', function (e) {
       var data = {
           id: $(e.currentTarget).data('question')
       };

        console.log(JSON.stringify(data));
       $.ajax({
          method: 'POST',
          url: 'http://127.0.0.1:8000/faq/like/',
           data: JSON.stringify(data)
       }).done(function (dataa) {
           $(e.currentTarget).closest('.like-dislike').find('.msg').html(dataa.message+' '+dataa.id);
       });
    });
});