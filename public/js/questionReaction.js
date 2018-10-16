(function (window, $) {
    'use strict';

    window.Reaction = function ($questionsDivBlock) {
        this.$questionsDivBlock = $questionsDivBlock;

        this.$questionsDivBlock.on(
            'click',
            '.fa-thumbs-up',
            this.questionLike.bind(this)
        );
    };

    $.extend(window.Reaction.prototype, {

       questionLike: function (e) {
         var $questionThumb = $(e.currentTarget);
         var datata = {
             id: $questionThumb.data('question')
         };

         $.ajax({
             url: 'http://127.0.0.1:8000/faq/reaction',
             method: 'POST',
            data: JSON.stringify(datata)
         }).done(function (dataa) {
             $questionThumb.closest('.js-like-dislike').find('.msg').html(dataa.message+' '+dataa.id);
             $questionThumb.css({'color':'green', 'font-size': '30px'});
             $questionThumb.prop('disabled', true);
         });
       }
    });

})(window, jQuery);