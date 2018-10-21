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
         var $block = $questionThumb.closest('.js-like-dislike');

         var $questionId = $questionThumb.closest('.card-body').data('question');

         var data = {
             questionId: $questionId,
             like1: $questionThumb.data('reaction'),
         };

         $.ajax({
             url: 'http://127.0.0.1:8000/faq/reaction',
             method: 'POST',
             data: JSON.stringify(data)
         }).done(function (data) {
             $block.find('.msg').html(data.message);
             $questionThumb.css({'color':'green', 'font-size': '30px'});
             $block.find('i.fa').prop('disabled', true);

         }).fail(function (jqXHR, textStatus, errorThrown) {
                alert(textStatus)
         });
       },

        dislikeFormSubmit: function (e) {
            e.preventDefault();
            var $form = $(e.currentTarget);
            var $block = $form.closest('.js-like-dislike');
            var $questionId = $form.closest('.card-body').data('question');

            $form.next().attr('hidden', true);

            var $formSerialize = $form.serializeArray();


            if($formSerialize.length === 0)
            {
                $form.next().attr('hidden', false);
                $form.next().html('EMPTY');
            }

            var formValues = {
                questionId: $questionId,
            };

            $.each($formSerialize, function (id, fieldData) {
                formValues[fieldData.name] = fieldData.value;
            });


            $.ajax({
                url: 'http://127.0.0.1:8000/faq/reaction',
                method: "POST",
                data: JSON.stringify(formValues)
            }).done(function (data) {
                $form.closest('.modal').modal('toggle');
                $block.find('.msg').html(data.message);
                $block.find('.fa-thumbs-down').css({'color':'red', 'font-size': '30px'});
                $block.find('i.fa').prop('disabled', true);
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

    });

})(window, jQuery);