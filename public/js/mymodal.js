/**
 * 模态窗口的部分代码，写得很恶心，再次证明写前端代码不是谁都能写的
 *
 * 这个类的用法是，在你想要点击弹出模态框的dom里加一个属性data-toggle=mymodal
 * 这个dom里可以加上data-title, data-content, data-method, data-url
 * 等参数来控制模态框的显示
 * 注意data-field为一个json数组，小心在赋值的时候对引号的用法
 *
 * 如果需要精确的控制button的动作，请绑定相应dom的click的行业为mymodal.show方法，
 * 支持的参数可以参照mymodal.params_default里的配置
 *
 * 另外，我这个js写得真烂
 */



//注册展示前的行为，把里面的方案都替换了
$("#myModal").on('show.bs.modal', function(){
    mymodal.assign(mymodal.params);
})

//在关闭的时候要恢复到原来的样子
$("#myModal").on('hidden.bs.modal', function(){
    if(mymodal.params.close_refresh){
        if(!mymodal.params.close_refresh_url || mymodal.params.close_refresh_url == '')
            location.reload();
        else
            location.href = mymodal.params.close_refresh_url;
        return;
    }
    mymodal.assign(mymodal.params_default);
    mymodal.params = '';
})

/**
 * 默认对这种类型的元素点击激活模态框，
 * 数据可以从dom里获取，但是button只能用默认buttons
 */
$("[data-toggle='mymodal']").click(function(){
    f = $(this).attr("data-field");
    p = {
        "title": $(this).attr("data-title"),
        "content": $(this).attr("data-content"),
        "method": $(this).attr("data-method"),
        "url": $(this).attr("data-url"),
        "field" : f ? $.parseJSON($(this).attr("data-field")) : {},
    };
    mymodal.show(p);
})

/**
 * 默认对这种类型的元素点击ajax提交当前表单
 * 返回值的json结构可以指定跳转的形式
 * 返回值的定义如下:
 * content表示呈现内容
 * title 标题
 * refresh是否刷新
 * refres_url跳转地址
 * button_name 按钮字体
 */
$("[data-toggle='ajaxsubmit']").click(function(){
        f = $(this).parents("form");
        if(f.attr("method") == "post"){
            fun = $.post;
        }else{
            fun = $.get;
        }
        fun(f.attr("action"),f.serialize(), function(data){
            try{
                d = $.parseJSON(data);
                if(d.refresh){
                    mymodal.refresh(d.content, d.refresh_url, d.title, d.button_name);
                }else {
                    mymodal.notice(d.content, d.title, d.button_name);
                }

            }catch(e){
                mymodal.notice(data, "数据返回异常");
            }
        });
        return false;
})

var mymodal = {
    //这是默认的模态框的样子
    params_default : {
        title: "确认",//标题
        content: "确认?",//内容
        method: "post", //提交的方法
        url: "", //提交的链接地址
        field:{},//提交的参数列表，当按钮里有commit的时候，会把这些参数提交到服务器
        close_refresh: false,//在关闭的时候跳转页面
        close_refresh_url: '',//在关闭的时候跳转页面地址，如果为空表示刷新本页
        buttons:[
        {
            ismark: false,//是否是默认按钮，即蓝色按钮
            type: "close",//按钮类型，可选值为close:普通关闭，refresh:刷新，submit:提交表单，根据url的设置提交到指定的地址，
            text: "取消"//按钮的文案
        },
        {
            ismark: true,
            type: "commit",
            text: "确认",
        }/*,
        {
            ismark: true,
            type: "refresh",
            text: "刷新",
        }
        */
        ]
    },
    //这是每一个配置都会替换掉的变量
    params: "",
    assign: function(p){
        if(!p){
            return;
        }
        if(p.title)
            $("#myModal .modal-title").html(p.title);
        if(p.content)
            $("#myModal .modal-body").html(p.content);

        f = $("#myModal form");
        if(p.url)
            f.attr("action", p.url);
        if(p.method)
            f.attr("method", p.method);
        if(p.field){
            template = $("<input type='hidden' />");
            $.each(p.field, function(k, v){
                i = template.clone();
                i.attr("name", k);
                i.attr("value", v);
                f.append(i);
            })

        }
        if(p.buttons){
            template = $("<button type='button'></button>");
            f.children().remove("button");
            $.each(p.buttons, function(k, v){
                b = template.clone()
                if(v.ismark){
                    b.attr("class", "btn btn-primary");
                }else{
                    b.attr("class", "btn btn-default");
                }
                if(v.type == "close"){
                    b.attr("data-dismiss", "modal");
                }else if(v.type == "commit"){
                    b.attr("type", "submit");
                    b.attr("name", "submit");
                }else if(v.type == "refresh"){
                    b.attr("onclick" , "javascript:location.reload();");
                }
                if(v.text){
                    b.text(v.text);
                }
                f.append(b);
            })
        }
    },
    //提供给外部调用的方法
    show : function (p){
        this.params= p
            $("#myModal").modal();
    },
    //为弹窗提示提供一种快捷的方法，这个模态框只有一个关闭按钮和一行文字
    //其中那一行文字可控
    notice : function(c){
        this.show({
            title: arguments[1] ? arguments[1] : "提示",//标题
            content: c,//内容
            method: "get", //提交的方法
            url: "", //提交的链接地址
            buttons:[
                {
                    ismark: true,//是否是默认按钮，即蓝色按钮
                    type: "close",//按钮类型，可选值为close:普通关闭，refresh:刷新，submit:提交表单，根据url的设置提交到指定的地址，
                    text: arguments[2] ? arguments[2] : "知道了"//按钮的文案
                }
            ]
        });
    },
    //用于可以刷新本页面的模态框
    refresh : function(c, r_u){
        this.show({
            title: arguments[2] ? arguments[2] : "提示",//标题
            content: c,//内容
            method: "get", //提交的方法
            url: "", //提交的链接地址
            close_refresh: true,
            close_refresh_url : r_u,
            buttons:[
                {
                    ismark: true,//是否是默认按钮，即蓝色按钮
                    type: "close",//按钮类型，可选值为close:普通关闭，refresh:刷新，submit:提交表单，根据url的设置提交到指定的地址，
                    text: arguments[3] ? arguments[3] : "知道了"//按钮的文案
                }
            ]
        });
    }
}
//把这个框的默认样式改成这样
mymodal.assign(mymodal.params_default);
