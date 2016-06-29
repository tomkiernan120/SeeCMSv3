// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

$(document).ready(function(){
 

  $(document).on("click", ".button", function(e){
    e.preventDefault();
    $('.nav-icon-sidebar').addClass('open');
    $(this).addClass('slide');
    $(".see-cms-toolbar").animate({ left: '0' }, 300);
  });

  $(document).on("click", ".button.slide", function(e){
    e.preventDefault();
    $(this).removeClass('slide');
    $('.nav-icon-sidebar').removeClass('open');
    $(".see-cms-toolbar").animate({ left: '-150px' }, 300); 
  });

  $(document).on("click", ".hideedit", function(e){
    e.preventDefault();
    $(this).addClass('hiddenedit');
    $(this).html( '<span><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></span>Show editing controls' );
    $('p.editbar').addClass('hideeditbar');
  });

  $(document).on("click", ".hideedit.hiddenedit", function(e){
    e.preventDefault();
    $(this).removeClass('hiddenedit');
    $(this).html( '<span><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></span>Hide editing controls' );
    $('p.editbar').removeClass('hideeditbar');
  });

});