// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

$(document).ready(function(){
 

  $(document).on("click", "a.see-cms-tool", function(e){
  	e.preventDefault();
  	$(".see-cms-toolbar").animate({ left: '0' }, 300);
    $(this).addClass('spin');
  });

  $(document).on("click", "a.spin", function(e){
    e.preventDefault();
    $(this).removeClass('spin');
    $(".see-cms-toolbar").animate({ left: '-150px' }, 300);
    
  });

});