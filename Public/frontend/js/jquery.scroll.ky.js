var ofset = $('.content-right').offset().top;
  $(document).on('scroll',function(){

    var scrolls = $(document).scrollTop();
    if(scrolls > ofset){
      $('.content-right').addClass('con-ri-fix');
    }else{
      $('.content-right').removeClass('con-ri-fix');
    }
  })