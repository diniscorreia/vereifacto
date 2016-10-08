// Get selected text to submit
// Otherwise, show alternative text field
chrome.tabs.executeScript( {
    code: "window.getSelection().toString();"
}, function(selection) {
    if(selection[0]) {
      $('#popup-text').find('textarea').val(selection[0]);
      $('#popup-comment').hide();
    } else {
      $('#popup-text').hide();
    }
});


// Get current url to submit
function getCurrentTabUrl(callback) {  
  var queryInfo = {
    active: true, 
    currentWindow: true
  };

  chrome.tabs.query(queryInfo, function(tabs) {
    var tab = tabs[0]; 
    var url = tab.url;
    callback(url);
  });
}

function renderURL(statusText) {
  // document.getElementById('status').textContent = statusText;

  $('#popup-url').val(statusText)
}

document.addEventListener('DOMContentLoaded', function() {
  getCurrentTabUrl(function(url) {
    renderURL(url); 
  });
});

$(function(){
  $('#popup-submit').on('click', function(e){
    e.preventDefault();
    var nonce = '';

    $.getJSON( "https://verifacto.net/api/get_nonce/?controller=posts&method=create_post", function( data ) {
      nonce = data.nonce;

      var post_content;

      if( !$('#popup-text').find('textarea').val() ) {
        post_content = encodeURIComponent( $('#popup-comment').find('textarea').val() );
      } else {
        post_content = encodeURIComponent( $('#popup-text').find('textarea').val() );
      }

      var post_source = encodeURIComponent( $('#popup-url').val() );

      $.getJSON( "https://verifacto.net/api/create_post/?nonce=" + nonce + "&status=draft&type=quote&title=Submiss%C3%A3o&custom_fields={%22quote_text%22:%22"+post_content+"%22,%22source%22:%22"+post_source+"%22}", function( data ) {

          $('#popup-form').hide();

          if( data.status == 'ok'  ) {  
            $('#callout-success').removeClass('hide');
          } else {
            $('#callout-alert').removeClass('hide');
          }

      });
    });
  });
});