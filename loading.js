
var g ={};
function init()
{
    $('#keywordInput').keyup(function()
    {
        g.keywordInput = $('#keywordInput').val();
        g.userSearch = $.ajax(
                {
                    type: 'GET',
                    url: 'ajaxHandler.php',
                    data: {"keywordInput" : g.keywordInput},
                    dataType: "text",
                    success: function(data)
                    {
                        
                        g.array = JSON.parse(data);
                        displaySuggestion(g.array);
                    }
                    
                    
            });
        
    });
    
   
}


/**
 * This function takes care of inserting suggestions in the option tags.
 * @param {type} array
 * @returns {undefined}
 */
function displaySuggestion(array)
{
   g.optionTags=$("#suggestions").children();
   
    if (array !== null)
        for (var i = 0; i < g.optionTags.length; i++){
            
           g.optionTags[i].value = array[i];
        }
}
    
    
    window.onload=init;