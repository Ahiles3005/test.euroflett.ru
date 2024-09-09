$.fn.setValuesByName = function(object){
    var $this = $(this);
    $.each(object, function(name, val){
        $this.find('[name=' + name + ']').val(val);
    });
    return $this;
}

/*$(function(){		
	$('.button-primary').on('click', function(e) {
		if($('input[name="ORDER_PROP_3"]').val()==""){
			$('input[name="ORDER_PROP_3"]').val($('input[name="ORDER_PROP_2"]').val() + "@mail.ru");
		}
		if($('input[name="ORDER_PROP_6"]').val()==""){
			$('input[name="ORDER_PROP_6"]').val($('input[name="ORDER_PROP_7"]').val() + "@mail.ru");
		}
	});
});*/

$(function(){
    $('form.js-simple-order').each(function(){
        var $form = $(this),
            loading = new function(){
                function setOpacity(opacity){
                    $form.css({opacity : opacity});
                }
                $.extend(this, {
                    start : function(){
                        setOpacity(0.3);
                    },
                    stop : function(){
                        setOpacity(1);
                    }
                });
            };

        if(!$form.ajaxSubmit){
            prompt('Для работы sale.order.ajax требуется jQuery Form Plugin. Вот его адрес:', 'http://malsup.com/jquery/form/');
            return;
        }

        $form
            .ajaxForm({
                beforeSubmit : function(){
                    loading.start();
                },
                success : function(response){
                    loading.stop();
                    try{
                        parseJson();
                    }
                    catch(e){
                        showResponse();
                    }
                    return false;

                    function parseJson(){
                        response = JSON.parse(response);
                        if(response.redirect){
                            window.top.location.href = response.redirect;
                        }
                    }

                    function showResponse(){
                        $form.html(response);
                        var $errors = $form.find('.js-errors');
                        if(!$errors.length){
                            return;
                        }
                        $('body, html').animate({
                            scrollTop : $errors.offset().top() - 500
                        }, 600);
                    }
                },
                dataType : 'text'
            })
            .on('change', '[name=email]', function(){
                mail=$(this).val();
                $form.setValuesByName({ORDER_PROP_3 : mail});
                $form.setValuesByName({ORDER_PROP_6 : mail});
            })
            .on('change', '[name=ORDER_PROP_2]', function(){
                mail=$(this).val()+'@automail.com';
                $form.setValuesByName({ORDER_PROP_3 : mail});
            })
            /*.on('change', '[name=ORDER_PROP_7]', function(){
                mail=$(this).val()+'@automail.com';
                $form.setValuesByName({ORDER_PROP_6 : mail});
            })*/
            .on('change', '[name=PROFILE_ID]', function(){
                $form.setValuesByName({
                    profile_change : 'Y',
                    confirmorder : 'N'
                }).submit();
            })
            .on('change', ':radio', function(){
                if(!$(this).is(':checked')){
                    return;
                }
                $form.setValuesByName({
                    confirmorder : 'N',
                    profile_change : 'N'
                }).submit();
            });
    });
});
