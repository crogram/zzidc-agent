var App = function () {
  /* 基础配置初始化默认加载 */
  var config = {
    tooltip: false,
    popover: false,
    nanoScroller: false,
    nestableLists: false,
    hiddenElements: false,
    bootstrapSwitch:false,
    dateTime:false,
    datepicker:false,
    select2:false,
    tags:false,
    slider:false
  }; 

  var voice_methods = [];
  /* Nestable Lists 嵌套列表*/
  var nestable = function(){
    $('.dd').nestable();
    //Watch for list changes and show serialized output
    function update_out(selector, sel2){
      var out = $(selector).nestable('serialize');
      $(sel2).html(window.JSON.stringify(out));
    }
    update_out('#list1',"#out1");
    update_out('#list2',"#out2");
    $('#list1').on('change', function() {
      update_out('#list1',"#out1");
    });
    $('#list2').on('change', function() {
      update_out('#list2',"#out2");
    });
  };//End of Nestable Lists

  /* Form Wizard 表单向导 */
  var wizard = function(){
    //Fuel UX
    $('.wizard-ux').wizard();
    $('.wizard-ux').on('changed',function(){
      //delete $.fn.slider;
      $('.bslider').slider();
    });
    $(".wizard-next").click(function(e){
      var id = $(this).data("wizard");
      $(id).wizard('next');
      e.preventDefault();
    });
    $(".wizard-previous").click(function(e){
      var id = $(this).data("wizard");
      $(id).wizard('previous');
      e.preventDefault();
    });
  };
  /* End of Form wizard 表单向导 */

  /* Form Masks 表单遮罩 */
  var masks = function(){
    $("[data-mask='date']").mask("99/99/9999");
    $("[data-mask='phone']").mask("(999) 999-9999");
    $("[data-mask='phone-ext']").mask("(999) 999-9999? x99999");
    $("[data-mask='phone-int']").mask("+33 999 999 999");
    $("[data-mask='taxid']").mask("99-9999999");
    $("[data-mask='ssn']").mask("999-99-9999");
    $("[data-mask='product-key']").mask("a*-999-a999");
    $("[data-mask='percent']").mask("99%");
    $("[data-mask='currency']").mask("$999,999,999.99");
  };
  /* End of Form Masks 表单遮罩 */

  /* Data Tables 数据表 */
  var dataTables = function(){
  	//Basic Instance
    $("#datatable").dataTable();
    
    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
    $('.dataTables_length select').addClass('form-control');    
  };
  /* End of dataTables 数据表 */

  /* Speech Recognition 语音识别 */
  var speech_commands = [];
  if(('webkitSpeechRecognition' in window)){
    var rec = new webkitSpeechRecognition();  
  }
  var speech = function(options){
    if(('webkitSpeechRecognition' in window)){
      if(options=="start"){
        rec.start();
      }else if(options=="stop"){
        rec.stop();
      }else{
        var config = {
          continuous: true,
          interim: true,
          lang: false,
          onEnd: false,
          onResult: false,
          onNoMatch: false,
          onSpeechStart: false,
          onSpeechEnd: false
        };
        $.extend( config, options );
        
        if(config.continuous){rec.continuous = true;}
        if(config.interim){rec.interimResults = true;}
        if(config.lang){rec.lang = config.lang;}        
        var values = false;
        var val_command = "";
        
        rec.onresult = function(event){
          for (var i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
              var command = event.results[i][0].transcript;//Return the voice command
              command = command.toLowerCase();//Lowercase
              command = command.replace(/^\s+|\s+$/g,'');//Trim spaces
              console.log(command);
              if(config.onResult){
                config.onResult(command);
              }   
              
              $.each(speech_commands,function(i, v){
                if(values){//Second command
                  if(val_command == v.command){
                    if(v.dictation){
                      if(command == v.dictationEndCommand){
                        values = false;
                        v.dictationEnd(command);
                      }else{
                        v.listen(command);
                      }
                    }else{
                      values = false;
                      v.listen(command);
                    }
                  }
                }else{//Primary command
                  if(v.command == command){
                    v.action(command);
                    if(v.listen){
                      values = true;
                      val_command = v.command;
                    }
                  }
                }
              });
            }else{
              var res = event.results[i][0].transcript;//Return the interim results
              $.each(speech_commands,function(i, v){
                if(v.interim !== false){
                  if(values){                
                    if(val_command == v.command){
                      v.interim(res);
                    }
                  }
                }
              });
            }
          }
        };      
        
        if(config.onNoMatch){rec.onnomatch = function(){config.onNoMatch();};}
        if(config.onSpeechStart){rec.onspeechstart = function(){config.onSpeechStart();};}
        if(config.onSpeechEnd){rec.onspeechend = function(){config.onSpeechEnd();};}
        rec.onaudiostart = function(){ $(".speech-button i").addClass("blur"); }
        rec.onend = function(){
          $(".speech-button i").removeClass("blur");
          if(config.onEnd){config.onEnd();}
        };
      }    
    }else{ 
      alert("Only Chrome25+ browser support voice recognition.");
    }
  };
  /* End of speech */

  var speechCommand = function(command, options){
    var config = {
      action: false,
      dictation: false,
      interim: false,
      dictationEnd: false,
      dictationEndCommand: 'final.',
      listen: false
    };
    $.extend( config, options );
    if(command){
      if(config.action){
        speech_commands.push({
          command: command, 
          dictation: config.dictation,
          dictationEnd: config.dictationEnd,
          dictationEndCommand: config.dictationEndCommand,
          interim: config.interim,
          action: config.action, 
          listen: config.listen 
        });
      }else{
        alert("Must have an action function");
      }
    }else{
      alert("Must have a command text");
    }
  };
  /* End of speechCommand */

  function toggleSideBar(_this){
    var b = $("#sidebar-collapse")[0];
    var w = $("#cl-wrapper");
    var s = $(".cl-sidebar");
    
    if(w.hasClass("sb-collapsed")){
      $(".fa",b).addClass("fa-angle-left").removeClass("fa-angle-right");
      w.removeClass("sb-collapsed");
    }else{
      $(".fa",b).removeClass("fa-angle-left").addClass("fa-angle-right");
      w.addClass("sb-collapsed");
    }
    //updateHeight();
  }

  function updateHeight(){
    if(!$("#cl-wrapper").hasClass("fixed-menu")){
      var button = $("#cl-wrapper .collapse-button").outerHeight();
      var navH = $("#head-nav").height();
      //var document = $(document).height();
      var cont = $("#pcont").height();
      var sidebar = ($(window).width() > 755 && $(window).width() < 963)?0:$("#cl-wrapper .menu-space .content").height();
      var windowH = $(window).height();
      
      if(sidebar < windowH && cont < windowH){
        if(($(window).width() > 755 && $(window).width() < 963)){
          var height = windowH;
        }else{
          var height = windowH - button - navH;
        }
      }else if((sidebar < cont && sidebar > windowH) || (sidebar < windowH && sidebar < cont)){
        var height = cont + button + navH;
      }else if(sidebar > windowH && sidebar > cont){
        var height = sidebar + button;
      }  
      // var height = ($("#pcont").height() < $(window).height())?$(window).height():$(document).height();
      $("#cl-wrapper .menu-space").css("min-height",height);
    }else{
      $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
    }
  }

  return {
    init: function (options) {
      /* Extends basic config with options 扩展基本配置项 */
      $.extend( config, options );
      
      /* VERTICAL MENU 垂直菜单 */
      $(".cl-vnavigation li ul").each(function(){
        $(this).parent().addClass("parent");
      });
      
      $(".cl-vnavigation li ul li.active").each(function(){
        $(this).parent().show().parent().addClass("open");
        //setTimeout(function(){updateHeight();},200);
      });
      
      $(".cl-vnavigation").delegate(".parent > a","click",function(e){
        $(".cl-vnavigation .parent.open > ul").not($(this).parent().find("ul")).slideUp(300, 'swing',function(){
           $(this).parent().removeClass("open");
        });
        
        var ul = $(this).parent().find("ul");
        ul.slideToggle(300, 'swing', function () {
          var p = $(this).parent();
          if(p.hasClass("open")){
            p.removeClass("open");
          }else{
            p.addClass("open");
          }
          //var menuH = $("#cl-wrapper .menu-space .content").height();
          // var height = ($(document).height() < $(window).height())?$(window).height():menuH;
          //updateHeight();
         $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
        });
        e.preventDefault();
      });
      
      /*Small devices toggle*/
      $(".cl-toggle").click(function(e){
        var ul = $(".cl-vnavigation");
        ul.slideToggle(300, 'swing', function () {
        });
        e.preventDefault();
      });
      
      /*Collapse sidebar*/
      $("#sidebar-collapse").click(function(){
          toggleSideBar();
      });

      if($("#cl-wrapper").hasClass("fixed-menu")){
        var scroll =  $("#cl-wrapper .menu-space");
        scroll.addClass("nano nscroller");
        function update_height(){
          var button = $("#cl-wrapper .collapse-button");
          var collapseH = button.outerHeight();
          var navH = $("#head-nav").height();
          var height = $(window).height() - ((button.is(":visible"))?collapseH:0) - navH;
          scroll.css("height",height);
          $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
        }

        $(window).resize(function() {
          update_height();
        });    

        update_height();
        $("#cl-wrapper .nscroller").nanoScroller({ preventPageScrolling: true });
      }else{
        $(window).resize(function(){
          //updateHeight();
        }); 
        //updateHeight();
      }

      /*SubMenu hover */
      var tool = $("<div id='sub-menu-nav' style='position:fixed;z-index:9999;'></div>");
    
      function showMenu(_this, e){
        if(($("#cl-wrapper").hasClass("sb-collapsed") || ($(window).width() > 755 && $(window).width() < 963)) && $("ul",_this).length > 0){   
          $(_this).removeClass("ocult");
          var menu = $("ul",_this);
          if(!$(".dropdown-header",_this).length){
            var head = '<li class="dropdown-header">' +  $(_this).children().html()  + "</li>" ;
            menu.prepend(head);
          }
          tool.appendTo("body");
          var top = ($(_this).offset().top + 8) - $(window).scrollTop();
          var left = $(_this).width();
          tool.css({
            'top': top,
            'left': left + 8
          });
          tool.html('<ul class="sub-menu">' + menu.html() + '</ul>');
          tool.show();
          menu.css('top', top);
        }else{
          tool.hide();
        }
      }

      $(".cl-vnavigation li").hover(function(e){
        showMenu(this, e);
      },function(e){
        tool.removeClass("over");
        setTimeout(function(){
          if(!tool.hasClass("over") && !$(".cl-vnavigation li:hover").length > 0){
            tool.hide();
          }
        },500);
      });

      tool.hover(function(e){
        $(this).addClass("over");
      },function(){
        $(this).removeClass("over");
        tool.fadeOut("fast");
      });
          
      $(document).click(function(){
        tool.hide();
      });
      $(document).on('touchstart click', function(e){
        tool.fadeOut("fast");
      });
      
      tool.click(function(e){
        e.stopPropagation();
      });
       
      $(".cl-vnavigation li").click(function(e){
        if((($("#cl-wrapper").hasClass("sb-collapsed") || ($(window).width() > 755 && $(window).width() < 963)) && $("ul",this).length > 0) && !($(window).width() < 755)){
          showMenu(this, e);
          e.stopPropagation();
        }
      });
        
      $(".cl-vnavigation li").on('touchstart click', function(){
        //alert($(window).width());
      });

      $(window).resize(function(){
        //updateHeight();
      });

      var domh = $("#pcont").height();
      $(document).bind('DOMSubtreeModified', function(){
        var h = $("#pcont").height();
        if(domh != h) {
          //updateHeight();
        }
      });
      
      /*Return to top*/
      var offset = 220;
      var duration = 500;
      var button = $('<a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>');
      button.appendTo("body");
      
      jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
      });

      jQuery('.back-to-top').click(function(event) {
          event.preventDefault();
          jQuery('html, body').animate({scrollTop: 0}, duration);
          return false;
      });
      
      /* Datepicker UI */
      if(config.datepicker){
        $( ".ui-datepicker" ).datepicker();
      }
      /*Tooltips*/
      if(config.tooltip){
        $('.ttip, [data-toggle="tooltip"]').tooltip();
      }
      
      /*Popover*/
      if(config.popover){
        $('[data-popover="popover"]').popover();
      }

      /*NanoScroller*/      
      if(config.nanoScroller){
        $(".nscroller").nanoScroller();     
      }

      /*Nestable Lists*/
      if(config.nestableLists){
        $('.dd').nestable();
      }

      /*Switch*/
      if(config.bootstrapSwitch){
        $('.switch').bootstrapSwitch();
      }
      
      /*DateTime Picker*/
      if(config.dateTime){
        $(".datetime").datetimepicker({autoclose: true});
      }
      
      /*Select2*/
      if(config.select2){
         $(".select2").select2({
          width: '100%'
         });
      }

       /*Tags*/
      if(config.tags){
        $(".tags").select2({tags: 0,width: '100%'});
      }
      
       /*Slider*/
      if(config.slider){
        $('.bslider').slider();     
      }

      /*Input & Radio Buttons*/
      if(jQuery().iCheck){
        $('.icheck').iCheck({
          checkboxClass: 'icheckbox_square-blue checkbox',
          radioClass: 'iradio_square-blue'
        });
      }

      /*Bind plugins on hidden elements*/
      if(config.hiddenElements){
      	/*Dropdown shown event*/
        $('.dropdown').on('shown.bs.dropdown', function () {
          $(".nscroller").nanoScroller();
        });
        /*Tabs refresh hidden elements*/
        $('.nav-tabs').on('shown.bs.tab', function (e) {
          $(".nscroller").nanoScroller();
        });
      }
    },

    speech: function(options){
      speech(options);
    },

    speechCommand: function(com, options){
      speechCommand(com, options);
    },

    toggleSideBar: function(){
      toggleSideBar();
    },

    nestableLists: function(){
      nestable();
    },

    wizard: function(){
      wizard();
    },
    
    masks: function(){
      masks();
    },

    dataTables: function(){
      dataTables();
    },
    
    maps: function(){
      maps();
    },
    
    charts: function(){
      charts();
    },

    widgets: function(){
      widgets();
    }
  };
}();

$(function(){
  //$("body").animate({opacity:1,'margin-left':0},500);
  $("body").css({opacity:1,'margin-left':0});
});