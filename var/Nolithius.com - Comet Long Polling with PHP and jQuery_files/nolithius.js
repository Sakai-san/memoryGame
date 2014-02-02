$(function()
{    
   $('#more-tags-button').click(function()
    {        
        // Close if open
        if($(this).hasClass('less'))
        {
            $('#more-tags').slideUp();
            
            $(this).removeClass('less');
            $(this).text('More...');
        }
        // Else open
        else
        {
            $('#more-tags').slideDown();
            
            $(this).addClass('less');
            $(this).text('Less...');            
        }
        
        return false;
    });
});