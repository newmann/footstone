// +----------------------------------------------------------------------
// | Footstoen [ WE CAN DO MORE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Newmannhu <Newmannhu@qq.com> <http://>
// +----------------------------------------------------------------------
// 处理所有的ajax数据
// 基于jQuery.Form组件完成
// #ft_ajaxoutmsg显示服务器返回的内容；
// ft_ajaxButton为需要自动绑定提交的button的属性；

;$(function(){

    //全局ajax处理
    $.ajaxSetup({
        complete: function (jqXHR) {
            //登录失效处理
            if (jqXHR.responseText.state === 'logout') {
                location.href = GV.URL.LOGIN;
            }
        },
        data: {
            __hash__: GV.TOKEN
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //请求失败处理
            alert(errorThrown ? errorThrown : '操作失败');
        }
    })

    if (/msie/.test(navigator.userAgent.toLowerCase())) {
        //ie 都不缓存
        $.ajaxSetup({
            cache: false
        });
    }
    //所有的ajax form提交,由于大多业务逻辑都是一样的，故统一处理
    var ajaxForm_list = $('form.J_ajaxForm');
    if (ajaxForm_list.length) {
      // Wind.use('ajaxForm', 'artDialog', function () {
        if (/msie/.test(navigator.userAgent.toLowerCase())) {
            //ie8及以下，表单中只有一个可见的input:text时，会整个页面会跳转提交
            ajaxForm_list.on('submit', function (e) {
                //表单中只有一个可见的input:text时，enter提交无效
                e.preventDefault();
            });
        }

        $('button.J_ajax_submit_btn').on('click', function (e) {

            var btn = $(this);
            var form = btn.parents('form.J_ajaxForm');

            //批量操作 判断选项
            if (btn.data('subcheck')) {
                btn.parent().find('span').remove();
                if (form.find('input.J_check:checked').length) {
                    var msg = btn.data('msg');
                    if (msg) {
                        art.dialog({
                            id: 'warning',
                            icon: 'warning',
                            content: btn.data('msg'),
                            cancelVal: '关闭',
                            cancel: function () {
                                //btn.data('subcheck', false);
                                //btn.click();
                            },
                            ok: function () {
                               btn.data('subcheck', false);
                               btn.click();
                            }
                        });
                    } else {
                        btn.data('subcheck', false);
                        btn.click();
                    }

                } else {
                    $('<span class="tips_error">请至少选择一项</span>').appendTo(btn.parent()).fadeIn('fast');
                }
                return false;
            };

            //ie处理placeholder提交问题
            if (/msie/.test(navigator.userAgent.toLowerCase())) {
                form.find('[placeholder]').each(function () {
                    var input = $(this);
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });
            };

            form.ajaxSubmit({
                url: btn.data('action') ? btn.data('action') : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                dataType: 'json',
                beforeSubmit: function (arr, $form, options) {
                    var text = btn.text();

                    //按钮文案、状态修改
                    btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                },
                success: function (data, statusText, xhr, $form) {
                    var text = btn.text();

                    //按钮文案、状态修改
                    btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
                    if (data.state === 'success') {
                        $('<span class="tips_success">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('slow').delay(1000).fadeOut(function () {
                            if (data.referer) {
                                //返回带跳转地址
                                if(window.parent.art){
                                    //iframe弹出页
                                    window.parent.location.href = data.referer;
                                }else{
                                    window.location.href = data.referer;
                                }
                            } else {
                                if(window.parent.art){
                                    reloadPage(window.parent);
                                }else{
                                    //刷新当前页
                                    reloadPage(window);
                                }
                            }
                        });
                    } else if (data.state === 'fail') {
                        $('<span class="tips_error">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('fast');
                        btn.removeProp('disabled').removeClass('disabled');
                    }
                }
            });

            e.preventDefault();
        // });
      })
    }

});
