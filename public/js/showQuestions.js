$(document).ready(function () {

    $('.js-test').on('click', function (e) {

        console.log("asd");
    });

    $('.fa-thumbs-up').on('click', function (e) {
       var data = {
           id: $(e.currentTarget).data('question')
       };

        console.log(JSON.stringify(data));
       $.ajax({
          method: 'POST',
          url: 'http://127.0.0.1:8000/faq/like',
           data: JSON.stringify(data)
       }).done(function (dataa) {
           $(e.currentTarget).closest('.like-dislike').find('.msg').html(dataa.message+' '+dataa.id);
       });
    });
   //$('.js-like-article').on('click', function (e) {
   //    e.preventDefault();
   //    var $link = $(e.currentTarget);
   //    $link.toggleClass('fa-heart-o').toggleClass('fa-heart');

   //    $.ajax({
   //        method: 'POST',
   //        url: $link.attr('href')
   //    }).done(function (data) {
   //        $('.js-like-article-count').html(data.hearts);
   //    });
   //});
});