$(function(){
    $(".ce  li > a").click(function(){
	     $(this).addClass("xz").parents().siblings().find("a").removeClass("xz");
	})

	var t = $(window).height(); 
	pp(t)
	$(window).scroll(function(){
	    var top = $(window).scrollTop(); 
	      //设置变量top,表示当前滚动条到顶部的值
      
        
		var tt = $(window).height();  
		pp(tt)  //设置变量tt,表示当前窗口高度的值
		var num =0;
		for(var n=0;n<7;n++)            
        {

        	if( tt > 600 ){
        		if(top >= n*tt && top <= (n+1)*tt)  //在此处通过判断滚动条到顶部的值和当前窗口高度的关系（当前窗口的n倍 <= top <= 当前窗口的n+1倍）来取得和导航索引值的对应关系
			   {
				    num=n;
				}
	        }else{
	        	if(top >= n*600 && top <= (n+1)*600)  //在此处通过判断滚动条到顶部的值和当前窗口高度的关系（当前窗口的n倍 <= top <= 当前窗口的n+1倍）来取得和导航索引值的对应关系
			   {
				    num=n;
				}
	        }
		     
			   $(".ce   li > a").removeClass("xz").eq(num).addClass("xz");     //先删除导航所有的选中状态，在通过上面判断中获得的导航索引值给当前导航加选中样式！
		}

	})
	function pp (h){
		$('.main-li').each(function(){
			if(h>600){
				$(this).height(h);
			}else{
				$(this).height(600);
			}
			
		})
	}
    $("#navon1").click(function(){
	   $("html,body").animate({scrollTop:$(".li1").offset().top},600);

	})
	$("#navon2").click(function(){
	   $("html,body").animate({scrollTop:$(".li2").offset().top},600);

	})
	$("#navon3").click(function(){
	   $("html,body").animate({scrollTop:$(".li3").offset().top},600);
	})
    $("#navon4").click(function(){
	  $("html,body").animate({scrollTop:$(".li4").offset().top},600);   
	})
	$("#navon5").click(function(){
	  $("html,body").animate({scrollTop:$(".li5").offset().top},600);   
	})
    $("#navon6").click(function(){
	  $("html,body").animate({scrollTop:$(".li6").offset().top},600);   
	})
	$("#navon7").click(function(){
	  $("html,body").animate({scrollTop:$(".li7").offset().top},600);   
	})
})


 




























