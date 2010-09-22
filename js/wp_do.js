jQuery(document).ready(function ($){

   var id = '#wp_do-ActionBox';
    
   // Hotkeys
   // Open Box
   $(document).bind('keydown', {combi:'/', disableInInput: true}, function (e) {

    init_WP_Do();
    e.stopPropagation();  
    e.preventDefault();
    return false;
   
   });

    // Close Box
   $(document).bind('keydown', {combi:'Esc', disableInInput: false}, function (e) {
    close_WP_Do();
   });


    function init_WP_Do() {

        // Get Window Sizes
        var winH = $(window).height();  
        var winW = $(window).width();  

        // Center DO Box
        $(id).css('top',  winH/2-$(id).height()/2);  
        $(id).css('left', winW/2-$(id).width()/2); 

        reset_WP_Do();
        show_WP_Do();
        autocomplete_WP_Do();

    }//end init_WP_Do()

    function reset_WP_Do() {
        $(id+' input[type=text]').val('');
    }//end reset_WP_Do()

    function show_WP_Do() {
        $(id).fadeIn();
        $(id+' input[type=text]').focus();
    }//end show_WP_Do()

    function close_WP_Do() {
        $(id).fadeOut();
    }//end close_WP_Do()

    function autocomplete_WP_Do() {

        var autocompleteURL = $(id+' form').attr('action');

        // Autocomplete Query
        $(id+' input[type=text]').autocomplete(autocompleteURL, {
            dataType: 'json',
            width: 300,
            scroll: true,
            scrollHeight: 300,
            extraParams: { action: 'wp_do_action' },
            parse: function(data) {
                var array = new Array();
                for(var i=0;i<data.length;i++)
                {
                    array[array.length] = { data: data[i], value: data[i], result: data[i].name };
                }
                return array;
            },
            formatItem: function (row) {
                return row.name;
            },
            formatResult: function (row) {
                return row.url;
            },
        });

        // Handle the result
        $(id+' input[type=text]').result(function (event, data, formatted) {
            window.location = data.url;
        });

        // Suppress form submission
        $(id+' form').submit(function (){ return false; });
    }//end autocomplete_WP_Do()

});
