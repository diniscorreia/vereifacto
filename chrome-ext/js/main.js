// Workaround for fonts not loading via CSS
// From http://stackoverflow.com/a/22842677/711426
var iconFont = document.createElement('style');
    iconFont.type = 'text/css';
    iconFont.textContent = '@font-face { font-family: verifacto; src: url("'
        + chrome.extension.getURL('icons/verifacto.woff?v=4.0.3')
        + '"); }';
document.head.appendChild(iconFont);

// Dummy function to extract data from classes
function getVal($selector) {
  var cls = $selector.attr('class').split(' ')[0];
  var clsNumber = cls.substr(cls.lastIndexOf("-")+1);
  return clsNumber;
}

$(function(){

  // Set some vars
  var quotes = [],
      db,
      matchedQuotes = [];

  // Load all quotes on database
  // TODO: limit post number or dtae range for reasonable performance
  $.getJSON( "http://verifacto.net/wordpress/?json=1&post_type=quote&include=id,url,custom_fields&custom_fields=quote_text", function( data ) {
    db = data;

    $.each(data.posts, function(index,quote){
      quotes.push( quote.custom_fields.quote_text[0] )
    });


    // Search document for strings matching quotes
    // TODO: improve selectors?
    $.each(quotes, function(index,quote){
      $('body').highlight( quote, {
        className: 'pblc-vf__quote--index-' + index + ' pblc-vf__quote pblc-vf'
      });
    });


    // Search the DOM again to find matching quotes
    // TODO: don't search the DOM for this, obviously... 
    $('body').find('span.pblc-vf__quote').each(function(i,e){
      var $this = $(this),
          quoteIndex = getVal( $this ),
          quoteId = data.posts[quoteIndex].id,
          quoteScore = 0,
          quoteScoreLabel = '';


      $this.attr('data-quote-id', quoteIndex ); 

      // Might be handy, but not being used now
      matchedQuotes.push( quoteId );

      // Go back to database and retrive factchecking info for matched quotes
      // Loop might not be necessary but there might be more than one quote per page
      $.getJSON( "http://verifacto.net/wordpress/?json=meta/get_posts_by_meta_query&quoteid=" + quoteId + "&include=id,custom_fields&custom_fields=fact_check_score", function( data ) {
          var factchecksNum = data.posts.length,
              factchecksSum = 0,
              factcheckMean = 0;

          $.each(data.posts, function(index,fact){
            factchecksSum += parseInt( fact.custom_fields.fact_check_score[0] );
          });

          factcheckMean = factchecksSum/factchecksNum;

          // Scores and stuff...
          if( factcheckMean === 5 ) 
          {
            quoteScore = 5;
            quoteScoreLabel = 'True';
          } else if ( factcheckMean < 5 ) {
            quoteScore = 4;
            quoteScoreLabel = 'Mostly true';
          } else if ( factcheckMean <= 4 ) {
            quoteScore = 3;
            quoteScoreLabel = 'Controversial';
          } else if ( factcheckMean <= 3 ) {
            quoteScore = 2;
            quoteScoreLabel = 'Mostly false';
          } else if ( factcheckMean <= 2 ) {
            quoteScore = 1;
            quoteScoreLabel = 'False';
          } 

          // Go back to DOM and append icons, color and tooltips
          $this
            .addClass('pblc-vf__quote--score' + quoteScore)
            .attr('data-factchecks-label', quoteScoreLabel)
            .attr('data-factchecks-count', factchecksNum)
            .on('click', function(e){
              e.stopPropagation();
              window.location = 'http://verifacto.net/?p=' + quoteId;
            })
            .append('<a href="http://verifacto.net/?p=' + quoteId +'"></a>');
          $this.webuiPopover({
            content: function(data){
                var html = '';
                html+='<span class="pblc-vf__popover__title score' + quoteScore + '">'+ quoteScoreLabel +'</span>';
                html+='<span class="pblc-vf__popover__context">'+ factchecksNum +' fact-checks contribute to this score.</span>';
                html+='<span class="pblc-vf__popover__note">Click to know more</span>';
                return html;
            },
            title:'Verifacto',
            trigger:'hover',
            container: $this
          });
       });
    });




  });
}); 