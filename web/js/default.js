$(document).ready(function(){
    var $placeholder = $('#placeholder-row');
    var paramsNumber = 1;
    var ERROR_UNKNOWN_MSG = 'Unexpected error occurred.';
    $('#request-form').on('submit', function(){
        var $ec = $('#error-container'), $uri = $('#uri');
        $('#status-container').hide();
        // validate required data:
        var uri = $.trim($uri.val()), params = {};
        if (!uri.length) {
            $uri.parents('.form-group').addClass('has-error');
            $ec.text('Please provide URI.').fadeIn();
            return false;
        } else {
            $uri.parents('.form-group').removeClass('has-error');
        }
        // fetch request parameters
        $('.param-row').each(function(){
            var $row = $(this),
                $fn = $row.find('.paramName'),
                $fv = $row.find('.paramValue'),
                n,
                v;
            if ($fn.length && (n = $fn.val())) {
                v = $fv.length ? $.trim($fv.val()) : '';
                params[n] = v;
            }
        });
        // submit data
        $('#submit-button').button('loading');
        $ec.hide();
        $.ajax({
            url:'?q=index/query',
            type:'post',
            data:{
                uri:uri,
                method:($('#type-post').is(':checked') ? 'post' : 'get'),
                params:JSON.stringify(params)
            },
            success:function(r){
                // handle the response
                if ('object' !== typeof r) {
                    r = JSON.parse(r);
                }
                if (r.hasOwnProperty('status')) {
                    if ('OK' === r.status) {
                        $('#response-data').show();
                        var $hb = $('#response-headers').children('tbody').empty();
                        var first = true, cssStatus;
                        $.each(r.headers, function(k,v) {
                            if (!first) {
                                $hb.append($('<tr></tr>').addClass('warning').append($('<td colspan="2">Redirect</td>')));
                            }
                            $.each(v, function(name,value) {
                                $hb.append($('<tr></tr>').append($('<td></td>').text(name)).append($('<td></td>').text(value)));
                            });
                            first = false;
                        });
                        if (r.code > 499) {
                            cssStatus = 'danger';
                        } else if (r.code > 399) {
                            cssStatus = 'warning';
                        } else if (r.code > 299) {
                            cssStatus = 'info';
                        } else {
                            cssStatus = 'success';
                        }
                        $('#status-container').removeAttr('class')
                                .addClass('alert alert-sm alert-' + cssStatus)
                                .text('Last HTTP code: ' + r.code)
                                .fadeIn();
                        var $respCont = $('#response-container');
                        if (r.body) {
                            var code = $respCont.text(r.body).html();
                            $respCont.html(code);
                        } else {
                            $respCont.text('{none}');
                        }
                    } else {
                        $ec.text(r.status ? r.status : ERROR_UNKNOWN_MSG).fadeIn();
                    }
                } else {
                    $ec.text(ERROR_UNKNOWN_MSG).fadeIn();
                }
            },
            error:function(){
                $ec.text(ERROR_UNKNOWN_MSG).fadeIn();
            },
            complete:function(){
                $('#submit-button').button('reset');
            }
        });
        return false;
    });
    $('#param-add-button').on('click', function(){
        if (paramsNumber >= 5) {
            return ;
        }

        var $row = $placeholder.clone().removeAttr('id');
        
        if (4 < ++paramsNumber) {
            $(this).attr('disabled', true);
        }
        
        $placeholder.find('input').each(function(){
            $(this).val('');
        });

        $row.find('button')
                .on('click', function(){
                    $(this).parents('.param-row').remove();
                    if (paramsNumber > 1) {
                        paramsNumber--;
                    }
                    $('#param-add-button').removeAttr('disabled');
                })
                .removeAttr('id')
                .removeClass('btn-success')
                .addClass('btn-danger')
                .text('Rem');
        $row.insertBefore($placeholder);
    });
});