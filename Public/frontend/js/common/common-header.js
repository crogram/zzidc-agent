!function() {    
    function init_menu() {    	
        var menu_dropdown_sidebar= $(".sidebar0").find("a");
        var menu_dropdown_sidebar1= $(".sidebar1").find("a");
        /* 控制子菜单内的二级的显示和隐藏 */
        function init_sub_menu() {
            var a, b, c = $(".sidebar0").find("a");
            sidebar_types = $(".item-sub0[sidebar-type]");            
            
            var aa,bb,cc= $(".sidebar1").find("a");            
            sidebar_types1 = $(".item-sub1[sidebar-type]");
            return {
                eventBind: function() {
                    this.initStatus();
                    this.initStatus1();
                    var speed = null;
                    c.mouseenter(function() {
                        var current = $(this);
                        current.data("enterTime", (new Date).getTime()),
                        /* 当鼠标滑动时，控制子菜单显示的速度 */
                        current != b && (speed = setTimeout(function() {
                            b.removeClass("active"),
                            current.addClass("active"),
                            b = current;
                            var sidebar_type = current.attr("sidebar-type"),
                            item_sub = $('.item-sub0[sidebar-type="' + sidebar_type + '"]');                            
                            a.hide(),                             
                            item_sub.show(),
                            a = item_sub,                            
                            current.parents(".menu-dropdown").css({
                                height: item_sub.height() + 20 + "px"
                            })
                        },
                        20))
                    }).mouseleave(function() {                    	
                        var current = $(this),                        
                        enterTime = parseInt(current.data("enterTime") || 0, 10),                       
                        currentTime = (new Date).getTime();                        
                        200 >= currentTime - enterTime && speed && (clearTimeout(speed), speed = null)
                    });
                    /*cc开始*/
                   cc.mouseenter(function() {
                        var current = $(this);
                        current.data("enterTime", (new Date).getTime()),
                        /* 当鼠标滑动时，控制子菜单显示的速度 */
                        current != b && (speed = setTimeout(function() {
                            bb.removeClass("active"),
                            current.addClass("active"),
                            bb = current;
                            var sidebar_type = current.attr("sidebar-type"),
                            item_sub = $('.item-sub1[sidebar-type="' + sidebar_type + '"]'); 
                            aa.hide(),                             
                            item_sub.show(),
                            aa = item_sub,                            
                            current.parents(".menu-dropdown").css({
                                height: item_sub.height() + 20 + "px"
                            })
                        },
                        20))
                    }).mouseleave(function() {                    	
                        var current = $(this),                        
                        enterTime = parseInt(current.data("enterTime") || 0, 10),                       
                        currentTime = (new Date).getTime();                        
                        200 >= currentTime - enterTime && speed && (clearTimeout(speed), speed = null)
                    });
                   /*cc结束*/
                    
                },
                initStatus: function() {
                    c.removeClass("active"),
                    sidebar_types.hide(),                     
                    a = $(".item-sub0[sidebar-type]").eq(0).show(),                   
                    b = menu_dropdown_sidebar.eq(0).addClass("active")
                }
                ,initStatus1: function() {                	
                    cc.removeClass("active"),
                    sidebar_types1.hide(),                  
                    aa = $(".item-sub1[sidebar-type]").eq(0).show(),                   
                    bb = menu_dropdown_sidebar1.eq(0).addClass("active")
                }
            }
        }
        
        /* 子菜单 */
        var ism = init_sub_menu();
        ism.eventBind();
        
        var clear_timeout = null;
        // $(".item-sub[sidebar-type]").eq(0);
        /* 控制导航显示菜单的显示及速度 */
        $("#J_common_header_menu .top-menu-item").mouseenter(function() {
            var mouseout_timer, speed = null,
            curr = $(this); (mouseout_timer = curr.data("mouseoutTimer")) && (clearTimeout(mouseout_timer), mouseout_timer = null),
            curr.data("enterTime", (new Date).getTime()),
            speed = setTimeout(function() {
            	clear_timeout && (clearTimeout(clear_timeout), clear_timeout = null);
                var has_dropdown = "true" === curr.attr("has-dropdown");
                var menu_type = curr.attr("menu-type");
                if (has_dropdown) {
                    var parent_position = curr.parent().position();
                    var current_position = curr.position();
                    var menu_dropdown = curr.find(".menu-dropdown");
                    menu_dropdown.css({
                        left: -(current_position.left - parent_position.left) + "px",
                        marginLeft: (current_position.left - parent_position.left) + "px"
                    });
                    var menu_dropdown_inner = menu_dropdown.find(".menu-dropdown-inner").height();
                    "product" != menu_type && (menu_dropdown_inner += 47),
                    menu_dropdown.addClass("animate").css({
                        height: menu_dropdown_inner + "px",
                        zIndex: 1
                    })
                }
            },
            20),
            curr.data("mouseoverTimter", speed)
        }).mouseleave(function(a) {
            var curr = $(this),
            enter_time = parseInt(curr.data("enterTime") || 0, 10),
            current_time = (new Date).getTime(),
            mouseover_timer = curr.data("mouseoverTimter");
            200 >= current_time - enter_time && mouseover_timer && (clearTimeout(mouseover_timer), mouseover_timer = null);
            var has_dropdown = "true" === curr.attr("has-dropdown");
            if (has_dropdown) {
                var timeout = setTimeout(function() {
                    var menu_dropdown = curr.find(".menu-dropdown");
                    menu_dropdown.removeClass("animate").css({
                        height: 0,
                        zIndex: 0
                    }),
                    curr.data("showing", !1),
                    ism.initStatus(),
                    mouseover_timer && (clearTimeout(mouseover_timer), mouseover_timer = null)
                },
                100);
                curr.data("mouseoutTimer", timeout)
            }
        });       
    }    
    function init() {
        $(function() {
            if (0 != $("#J_common_header_menu").length) {
            	init_menu();
            }
        })
    }    
    init();	
} ();