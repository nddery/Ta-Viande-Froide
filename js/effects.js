jQuery(document).ready(function($) {
   $("a.gallery-item").next("br").remove();
   $("a.gallery-item").mouseover(function(e) {
      $("body").append("<img id='jquery-center' class='features-hover' src='"+$(this).attr("rel")+"' alt='' />");
      $("img.features-hover").css({display:"none", visibility:"visible"}).fadeIn(350);
      $('#jquery-center').center(true);
   }).mouseout(function() {
      $(this).children("a.gallery-item img").stop().animate({opacity: 1, top: "0", left: "0"}, "fast");
      $("img.features-hover").remove();
   });
   // $('#header ul li a').click(function(){
   //    var thisClass = $(this).attr("rel");
   //    $("div.slide").stop(true, true).css('display', 'none');
   //    $("."+thisClass).animate({opacity: "show", height: "show"}, "slow");
   //    $('#header ul li.active').removeClass("active");
   //    $(this).parent("li").addClass("active");
   //    return false;
   // });

   // center element
   // $('#jquery-center').center();
   // $('#jquery-center').center(true);
});
