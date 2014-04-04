var page = {
    //从一个字段获取他的名字
    get_name: function (field){
        if(partner_desc && partner_desc[field + "-name"]){
            name = partner_desc[field + "-name"];
        }else{
            name = field;
        }
        return name;
    },
    get_desc: function(field){
        if(partner_desc && partner_desc[field + "-desc"]){
            desc = partner_desc[field + "-desc"];
        }else{
            desc = field;
        }
        return desc;
    },
    /**
     * 用来生成一行输入框
     */
    gen_form_input: function(field, value){
        template = $(heredoc(function(){/*
               <div class="form-group">
                   <label class="col-sm-2 control-label"></label>
                   <div class="col-sm-9">
                       <input type="text" class="form-control">
                       <span class="help-block">
                       </span>
                   </div>
                   <div class="col-sm-1 control-label"><button type="button" class="close" onclick="javascript:$(this).parents('.form-group').remove()">×</button></div>
               </div>
        */}));
        name = page.get_name(field);
        desc = page.get_desc(field);
        d = template.clone()
        d.find("label").attr("title", field).html(name);
        d.find("input").attr({
            "name" : field,
            "value" : value,
            "title" : desc,
            "placeholder" : desc,
        });
        if(arguments[2]){
            d.addClass("text-success");
        }
        if(hosts){
            if(hosts[field]){
                h = "指向的hosts:" + hosts[field];
            }else if(hosts[field] === false){
                h = "未设置hosts";
            }else{
                h = "";
            }
            d.find(".help-block").html(h);
        }
        return d;
    },
    //将生成好的dom插入到文档中使用一个hr做为标记符
    insert_form_input: function(field, value){
        $("#TAG_form_input_end").before(page.gen_form_input(field, value, arguments[2]));
    },
    //用传进来的合作方信息来初使化页面，注意这个函数不可重复调用
    init_form_area: function(pi){
        if(pi){
            $.each(pi, function(k, v){
                page.insert_form_input(k, v);
            });
        }
    },
    //根据给的数组生成添加字段下面的提示列表
    gen_add_dropdown: function(fa){
        if(!fa)
            return ;
        result = [];
        template = $("<li><a></a></li>");
        $.each(fa, function(k, v){
            name = page.get_name(k);
            l = template.clone();
            l.find("a").attr({
                data : k,
                href : "javascript:$('#addkey').val('" + k + "');"
            }).html(name + "(" + k + ")");
            result.push(l);
        });
        return result;
    },
    //在添加字段处有一个下拉框，每一次写入内容的时候更新一下那个框
    gen_add_dropdown_by_input: function(input){
        fa = {};//过滤后的数组，根据用户输入
        $.each(field_avail, function(k, v){
            if(k.indexOf(input) == 0){
                fa[k] = true;
            }
        });
        return page.gen_add_dropdown(fa);
    }

}
