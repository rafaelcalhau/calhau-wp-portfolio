/*
* Plugin Name:       Calhau WP Portfolio
* Description:       Plugin for creating a simple portfolio to wordpress websites.
* Plugin URI:        https://calhau.me
* Author:            Rafael Calhau
* Author URI:        https://calhau.me
* Tags:              portfolio
* Version:           1.1
* Text Domain:       calhau-portfolio
* License:           GNU Public License 2.0
*/

$(function() {

    $( document )
        .on('click', '#portfolioModal .bt-close-modal', function() {
            var modal = $('#portfolioModal')
            
            modal.animate({ opacity: 0, top: "+=100" }, 300, 'easeOutQuint', function() {
                modal.remove()
            })
        })

    $('a[rel="portfolio-modal"]')
        .on('click', function() {
            
            if ($('#portfolioModal').length !== 0)
            {
                $('#portfolioModal').remove()
            }

            $('body').append( 
                '<div id="portfolioModal">' + 
                '   <span class="bt-close-modal">' +
                '       <i class="remove icon"></i>' +
                '   </span>' +
                '   <div class="ui grid canvas-content"></div>' +
                '</div>'
            )

            var apiurl = "/wp-json/calhau-portfolio/v1/item/" + $(this).data('id')
            var modal = $('#portfolioModal')
            var canvas = modal.find( '.canvas-content' )
            
            webapp.api
                .setBaseURL( 'https://calhau.me/' )
                .get( apiurl, function(data) {
                    canvas.html( webapp.portfolio.render( data ) )
                    var topPos = "calc(50% - "+ (Math.floor(modal.outerHeight()/2)) +"px)"
                    
                    modal.css('top', topPos)
                    modal.animate({ opacity: 1 }, 300, 'easeOutQuint')
                } )
        
        })

})