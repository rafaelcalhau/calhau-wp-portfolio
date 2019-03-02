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

var webapp = function() {
    this.apiCore = function() {
        var api = this
        this.baseURL = null

        this.get = function(url, callback) {
            $.ajax({
                url: api.baseURL + url,
                success: function(data) {
                    callback(data)
                }
            })
        }
    }

    this.apiCore.prototype.setBaseURL = function(url) {
        this.baseURL = url

        return this
    }

    this.portfolio = {
        render: function(data) {
            
            var html = 
                '<div class="sixteen wide column">' +
                '   <h1 class="ui header">' +
                        data.project_name +
                '   </h1>' +
                '   <div class="ui divider"></div>' +
                        data.description +
                '   </div>'

            if (data.project_url !== "") {
                html +=
                    '<div class="sixteen wide column right aligned">' +
                    '   <i class="globe icon"></i> ' +
                    '   <a href="'+ data.project_url +'" target="_blank">' +
                            data.project_url
                    '   </a>'
                    '</div>'
            }

            html += '</div>'

            return html

        }
    }

    this.api = new this.apiCore()
}

window.webapp = new webapp()