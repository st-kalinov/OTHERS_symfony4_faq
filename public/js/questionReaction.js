(function (window, $) {
    'use strict';

    window.Reaction = function ($questionsDivBlock) {
        this.$questionsDivBlock = $questionsDivBlock;

        this.$questionsDivBlock.on(
            'click',
            '.fa-thumbs-up',
            this.questionLike.bind(this)
        );

        this.$questionsDivBlock.on(
            'click',
            '.btn-info',
            this.questionStatistic.bind(this)
        );

        this.$questionsDivBlock.on(
          'submit',
          '.js-dislike-form',
          this.dislikeFormSubmit.bind(this)
        );
    };

    $.extend(window.Reaction.prototype, {

       questionLike: function (e) {
         var $questionThumb = $(e.currentTarget);
         var datata = {
             id: $questionThumb.data('reaction')
         };

         $.ajax({
             url: 'http://127.0.0.1:8000/faq/reaction/like',
             method: 'POST',
             data: JSON.stringify(datata)
         }).done(function (dataa) {
             $questionThumb.closest('.js-like-dislike').find('.msg').html('Thanks');
             $questionThumb.css({'color':'green', 'font-size': '30px'});
             $questionThumb.prop('disabled', true);
         }).fail(function () {
             alert("FAIL");
         });
       },

        questionStatistic: function (e) {
             var $statisticBtn = $(e.currentTarget);
             var data = {
                id: $statisticBtn.data('id')
             };

             $.ajax({
                 url: 'http://127.0.0.1:8000/faq/statistic/question-statistic',
                 method: 'POST',
                 data: JSON.stringify(data)
             }).done(function (dataa) {
               $.each(dataa.statistic, function (category, reason) {
                   console.log(category);
                   $.each(reason, function (name, count) {
                       console.log(name+'---->'+count);
                   })
               })
             }).fail(function () {
                 alert("FAIL");
             });
        },

        dislikeFormSubmit: function (e) {
            e.preventDefault();
            var $form = $(e.currentTarget);
            $form.next().attr('hidden', true);

            var $formSerialize = $(e.currentTarget).serializeArray();
            if($formSerialize.length === 0)
            {
                $form.next().attr('hidden', false);
                $form.next().html('EMPTY');
            }

           var formValues = {id: $form.data('question')};
           $.each($formSerialize, function (id, fieldData) {
               formValues[fieldData.name] = fieldData.value;
           });

           console.log(formValues);
           //$.ajax({
           //   url: 'http://127.0.0.1:8000/faq/reaction/dislike',
           //    method: "POST",
           //    data: JSON.stringify(formValues)
           //}).done(function (data) {
//
           //}).fail(function () {
           //   alert("FAIL");
           //});
        }

    });

})(window, jQuery);